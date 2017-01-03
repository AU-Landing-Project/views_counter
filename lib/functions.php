<?php

namespace AU\ViewsCounter;

/**
 * Increments counter of views by the current user
 *
 * @param int  $entity_guid GUID of a viewed entity
 * @param bool $add_log     Create a separate annotation for this view
 * @return bool
 */
function increment_views_counter($entity_guid, $add_log = false) {

	$entity_guid = (int) $entity_guid;

	// Views by logged out users will be tracked by unonwed annotations (owner_guid = 0)
	$user_guid = (int) elgg_get_logged_in_user_guid();

	$options = array(
		'guids' => array($entity_guid),
		'annotation_names' => array('views_counter'),
		'annotation_owner_guids' => array($user_guid)
	);

	$views_counter = elgg_get_annotations($options);

	// Update the last view time for this entity to the current time
	update_last_view_time($entity_guid, $user_guid);

	if ($views_counter) {
		$counter = array_shift($views_counter);
		$result = update_annotation($counter->id, 'views_counter', $counter->value + 1, 'integer', $user_guid, ACCESS_PUBLIC);
	} else {
		$result = (bool) create_annotation($entity_guid, 'views_counter', 1, 'integer', $user_guid, ACCESS_PUBLIC);
	}

	if ($add_log && $result) {
		$impression = array(
			'time' => time(),
			'ip_address' => get_ip(),
			'user_guid' => $user_guid,
			'page_url' => get_input('referrer_url', current_page_url()),
		);
		$params = [
			'entity_guid' => $entity_guid,
		];
		$impression = elgg_trigger_plugin_hook('views_counter', 'user_data', $params, $impression);
		create_annotation($entity_guid, 'views_counter_log', serialize($impression), '', $user_guid, ACCESS_PUBLIC);
	}

	return $result;
}

/**
 * Update the last view time for this entity to the current time
 * 
 * @param int $entity_guid GUID of the viewed entity
 * @param int $user_guid   GUID of the viewer
 * @return bool
 */
function update_last_view_time($entity_guid, $user_guid = null) {

	$entity_guid = (int) $entity_guid;

	if (!isset($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	$user_guid = (int) $user_guid;

	// Get the last view annotation that has the last view time saved
	$options = array(
		'guids' => array($entity_guid),
		'annotation_owner_guids' => array($user_guid),
		'annotation_names' => array('last_view_time')
	);

	$last_view_time = elgg_get_annotations($options);

	if ($last_view_time) {
		$last_view_time = array_shift($last_view_time);

		// Update the value of last view time with the current time
		return update_annotation($last_view_time->id, 'last_view_time', time(), 'integer', $user_guid, 2);
	} else {
		// Create one annotation with the current time that means the last view time
		return create_annotation($entity_guid, 'last_view_time', time(), 'integer', $user_guid, 2);
	}
}

/**
 * Get the number of entity views
 * 
 * @param int       $entity_guid GUID of the entity
 * @param int|false $user_guid   GUID of the viewer
 *                               If set to false, will get cumulative views for all users
 *                               If set to 0, will get the guest views
 * @return int
 */
function get_views_counter($entity_guid, $user_guid = false) {

	$entity_guid = (int) $entity_guid;

	$options = array(
		'guids' => array($entity_guid),
		'annotation_names' => array('views_counter'),
		'annotation_calculation' => 'sum'
	);

	if ($user_guid !== false) {
		$user_guid = (int) $user_guid;
		$options['annotation_owner_guid'] = $user_guid;
	}

	$views_counter = elgg_get_annotations($options);

	return (int) $views_counter;
}

/**
 * Setup view counters by extending entity listing views
 * 
 * @return void
 */
function set_views_counter() {

	$subtypes = get_valid_types_for_views_counter();

	foreach ($subtypes as $subtype) {
		switch ($subtype) {
			case 'user':
				elgg_extend_view('profile/details', 'views_counter_pageowner', 490);
				break;

			case 'group':
				elgg_extend_view('groups/profile/layout', 'views_counter_pageowner', 490);
				break;

			case 'au_set':
				elgg_extend_view('page/layouts/au_configurable_widgets', 'views_counter_pageowner', 490);
				break;

			default:
				elgg_extend_view("object/$subtype", 'views_counter', 490);
				break;
		}
	}
}

/**
 * Returns entity types suitable for view tracking
 * @return array
 */
function get_valid_types_for_views_counter() {

	static $valid_types;

	if (isset($valid_types)) {
		return $valid_types;
	}

	$valid_types = ['user', 'group'];

	$dbprefix = elgg_get_config('dbprefix');
	$sql = "
		SELECT subtype
		FROM {$dbprefix}entity_subtypes
		WHERE type = :type
		AND subtype NOT IN ('plugin', 'admin_notice', 'elgg_upgrade')
	";
	$params = [':type' => 'object'];
	$rows = get_data($sql, null, $params);

	foreach ($rows as $row) {
		$valid_types[] = $row->subtype;
	}

	return $valid_types;
}

/**
 * Verify that view counting is enabled for the given entity and increment the count
 * 
 * @param int $entity_guid GUID of the entity
 * @return bool
 */
function add_views_counter($entity_guid) {

	// Cache guids so that we don't log view more than once when page is drawn
	static $logged_entities;

	if (!is_array($logged_entities)) {
		$logged_entities = [];
	}

	if (in_array($entity_guid, $logged_entities)) {
		return false;
	}

	$logged_entities[] = $entity_guid;

	$entity = get_entity($entity_guid);

	if (!$entity) {
		return false;
	}

	if ($entity->type == 'object') {
		$subtype = $entity->getSubtype();
	} else {
		$subtype = $entity->type;
	}

	// give plugins an option to use custom views if necessary
	$params = array('subtype' => $subtype);
	$handled = elgg_trigger_plugin_hook('views_counter', 'set_counter', $params, false);
	if ($handled) {
		return;
	}

	if (is_views_counter_enabled($entity)) {
		$logged_types = unserialize(elgg_get_plugin_setting('add_views_logger', PLUGIN_ID));
		$add_log = in_array($subtype, $logged_types);
		return increment_views_counter($entity->guid, $add_log);
	}

	return false;
}

/**
 * Add clauses to ege* options to sort entities by views count
 * 
 * @param array $options ege* options
 * @return array
 */
function add_views_counter_clauses(array $options = []) {

	$dbprefix = elgg_get_config('dbprefix');

	// Select the sum of the views counter returned by the JOIN
	$select = 'sum(ms.string) as views_counter';
	if (is_array($options['selects'])) {
		$options['selects'][] = $select;
	} else if ($options['selects']) {
		$options['selects'] = array($options['selects'], $select);
	} else {
		$options['selects'] = array($select);
	}

	// Get the annotations "views_counter" for each entity
	$metastring_id = elgg_get_metastring_id('views_counter');
	$join = 'LEFT JOIN ' . $dbprefix . 'annotations a ON a.entity_guid = e.guid AND a.name_id = ' . $metastring_id;
	if (is_array($options['joins'])) {
		$options['joins'][] = $join;
	} else if ($options['joins']) {
		$options['joins'] = array($options['joins'], $join);
	} else {
		$options['joins'] = array($join);
	}

	// JOIN the value of the annotations. The value of each views counter...
	$options['joins'][] = 'LEFT JOIN ' . $dbprefix . 'metastrings ms ON a.entity_guid = e.guid AND a.name_id = ' . $metastring_id . ' AND a.value_id = ms.id';

	// Check if the user does not want to list by best average any value different of: 'desc'
	if ($options['order_by'] != 'asc') {
		$options['order_by'] = ' views_counter desc, e.time_created desc';
	} else {
		$options['order_by'] = ' views_counter asc, e.time_created desc';
	}

	// Group the result of JOIN annotations by entity because each entity may have infinite annotations "generic_rate"
	$options['group_by'] .= ' e.guid ';

	return $options;
}

/**
 * Return an array of entities ordered by the number of views
 * 
 * @param array $options ege* options
 * @return ElggEntity[]|int|false
 */
function get_entities_by_views_counter(array $options = []) {
	$options = add_views_counter_clauses($options);
	return elgg_get_entities($options);
}

/**
 * The the last time the user viewed the entity
 * 
 * @param int $entity_guid GUID of the entity
 * @param int $user_guid   GUID of the viewer
 *                         If not set, will default to logged in user
 * @return int|false Timestamp, or false if no views were logged
 */
function get_last_view_time($entity_guid, $user_guid = null) {

	$entity_guid = (int) $entity_guid;

	if (!isset($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	$user_guid = (int) $user_guid;

	$options = array(
		'guids' => array($entity_guid),
		'annotation_names' => array('last_view_time'),
		'annotation_owner_guids' => array($user_guid)
	);

	$last_view_time = elgg_get_annotations($options);

	if ($last_view_time) {
		$last_view_time = array_shift($last_view_time);
		return (int) $last_view_time->value;
	}

	return false;
}

/**
 * Check if the user did see the last update for an entity based on
 * the last view time annotation and the updated time for the elgg entity
 * 
 * @param int $entity_guid GUID of the entity
 * @param int $user_guid   GUID of the viewer
 * @return bool|void
 */
function did_see_last_update($entity_guid, $user_guid = null) {
	$entity = get_entity($entity_guid);

	if (!$entity) {
		return;
	}

	$last_view_time = get_last_view_time($entity->guid, $user_guid);
	if ($last_view_time === false) {
		return false; // hasn't seen entity yet
	}

	return ($entity->time_updated < $last_view_time);
}

/**
 * Returns the IP address of the current user
 * @return string
 */
function get_ip() {
	if (getenv('HTTP_CLIENT_IP')) {
		$ip_address = getenv('HTTP_CLIENT_IP');
	} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
		$ip_address = getenv('HTTP_X_FORWARDED_FOR');
	} elseif (getenv('HTTP_X_FORWARDED')) {
		$ip_address = getenv('HTTP_X_FORWARDED');
	} elseif (getenv('HTTP_FORWARDED_FOR')) {
		$ip_address = getenv('HTTP_FORWARDED_FOR');
	} elseif (getenv('HTTP_FORWARDED')) {
		$ip_address = getenv('HTTP_FORWARDED');
	} else {
		$ip_address = $_SERVER['REMOTE_ADDR'];
	}

	return $ip_address;
}

/**
 * Check if views counter is enabled for an entity
 * 
 * @param \ElggEntity $entity Entity
 * @return bool
 */
function is_views_counter_enabled(\ElggEntity $entity) {

	if ($entity->type == 'object') {
		$subtype = $entity->getSubtype();
	} else {
		$subtype = $entity->type;
	}

	$added_types_setting = elgg_get_plugin_setting('add_views_counter', PLUGIN_ID);
	$removed_types_setting = elgg_get_plugin_setting('remove_views_counter', PLUGIN_ID);

	$added_types = $added_types_setting ? unserialize($added_types_setting) : [];
	$removed_types = $removed_types_setting ? unserialize($removed_types_setting) : [];

	if (in_array($subtype, $added_types)) {
		return true;
	} else if (!in_array($subtype, $removed_types)) {
		// If the views counter is being added for a subtype that was not set up
		// by the admin then let's set the plugin setting now
		if (!in_array($subtype, $added_types)) {
			$added_types[] = $subtype;
			elgg_set_plugin_setting('add_views_counter', serialize($added_types), PLUGIN_ID);
		}

		return true;
	}

	return false;
}
