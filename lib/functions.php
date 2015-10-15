<?php

namespace AU\ViewsCounter;

/**
 * Increment the views counter for any elgg entity
 */
function increment_views_counter($entity_guid) {
	$entity = get_entity($entity_guid);

	if ($entity) {
		$user = elgg_get_logged_in_user_entity();
		// If there is no loggedin user then lets updated the common views counter for all not loggedin users that has owner equal zero
		$user_guid = ($user) ? ($user->guid) : 0;

		$options = array(
			'types' => array($entity->type),
			'guids' => array($entity_guid),
			'annotation_names' => array('views_counter'),
			'annotation_owner_guids' => array($user_guid)
		);

		if ($entity->getSubtype()) {
			$options['subtypes'] = array($entity->getSubtype());
		}

		$views_counter = elgg_get_annotations($options);

		// Update the last view time for this entity to the current time
		update_last_view_time($entity_guid, $user_guid);

		if ($views_counter) {
			return update_annotation($views_counter[0]->id, 'views_counter', $views_counter[0]->value + 1, 'integer', $user_guid, ACCESS_PUBLIC);
		} else {
			return create_annotation($entity->guid, 'views_counter', 1, 'integer', $user_guid, ACCESS_PUBLIC);
		}
	}
}

/**
 * Update the last view time for this entity to the current time
 * 
 * @param $entity_guid
 * @param $user_guid
 */
function update_last_view_time($entity_guid, $user_guid) {
	$entity = get_entity($entity_guid);
	if (!$user = get_entity($user_guid)) {
		$user = elgg_get_logged_in_user_entity();
	}

	if ($entity && $user) {
		// Get the last view annotation that has the last view time saved
		$options = array(
			'types' => array($entity->type),
			'guids' => array($entity_guid),
			'annotation_owner_guids' => array($user->guid),
			'annotation_names' => array('last_view_time')
		);

		if ($entity->getSubtype()) {
			$options['subtypes'] = array($entity->getSubtype());
		}

		$last_view_time = elgg_get_annotations($options);
		// It should exists only one annotation that has the last view time
		$last_view_time = $last_view_time[0];

		if ($last_view_time) {
			// Update the value of last view time with the current time
			return update_annotation($last_view_time->id, 'last_view_time', time(), 'integer', $user->guid, 2);
		} else {
			// Create one annotation with the current time that means the last view time
			return create_annotation($entity->guid, 'last_view_time', time(), 'integer', $user->guid, 2);
		}
	}
}

/**
 * Get the number of views for an elgg entity
 * 
 * @param unknown_type $entity_guid
 * @param unknown_type $owner_guid
 * @return Ambigous <number, boolean>
 */
function get_views_counter($entity_guid, $owner_guid = 0) {
	$options = array(
		'guids' => array($entity_guid),
		'annotation_names' => array('views_counter'),
		'annotation_calculation' => 'sum'
	);

	if ($owner_guid) {
		$options['annotation_owner_guid'] = $owner_guid;
	}

	$views_counter = elgg_get_annotations($options);

	return $views_counter ? $views_counter : 0;
}

/**
 * Try to set the views counter on the views files based on the pattern followed by elgg for displaying entities
 */
function set_views_counter() {

	$vc_list = elgg_get_plugin_setting('add_views_counter', PLUGIN_ID);
	if ($vc_list) {
		$add_views_counter = unserialize($vc_list);
	}
	else {
		$add_views_counter = array();
	}
	
	if (is_array($add_views_counter) && count($add_views_counter)) {
		foreach ($add_views_counter as $subtype) {
			
			// give plugins an option to use custom views if necessary
			$params = array('subtype' => $subtype);
			$handled = elgg_trigger_plugin_hook('views_counter', 'set_counter', $params, false);
			if ($handled) {
				continue; // already taken care of
			}
			
			switch ($subtype) {
				case '':
					break;
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
					elgg_extend_view('object/' . $subtype, 'views_counter', 490);
					break;
			}
		}
	}
}

/**
 * Get the valid types for add views counter plugin
 */
function get_valid_types_for_views_counter() {
	$statistics = get_entity_statistics();

	$valid_types = array('user', 'group');
	foreach ($statistics['object'] as $subtype => $counter) {
		if ($subtype != 'plugin' && $subtype != '__base__') {
			$valid_types[] = $subtype;
		}
	}

	return $valid_types;
}

/**
 * Try to add the views counter for an entity based on the settings of the views_counter plugin
 * 
 * @param $entity_guid
 */
function add_views_counter($entity_guid) {
	static $added_entities;
	
	if (!is_array($added_entities)) {
		$added_entities = array();
	}
	
	if (in_array($entity_guid, $added_entities)) {
		return false; // we've already added this view
	}
	
	$entity = get_entity($entity_guid);
	
	if (!$entity) {
		return false;
	}

	// save this guid so we know it's been processed in the future
	$added_entities[] = $entity_guid;
	

	// Get the added types for add a views counter
	$added_types = unserialize(elgg_get_plugin_setting('add_views_counter', PLUGIN_ID));

	// Get the types set up by the admin to not allow the views counter
	$removed_types = unserialize(elgg_get_plugin_setting('remove_views_counter', PLUGIN_ID));

	// Save the subtype
	$subtype = $entity->getSubtype();
	$subtype = ($subtype) ? ($subtype) : $entity->type;

	// If the entity has a added type then increment the views counter
	if (in_array($subtype, $added_types)) {
		return increment_views_counter($entity->guid);
	} else if (!in_array($subtype, $removed_types)) {

		// If the views counter is being added for a subtype that was not set up by the admin then let's set the plugin setting now
		if (!in_array($subtype, $added_types)) {
			$added_types[] = $subtype;
			elgg_set_plugin_setting('add_views_counter', serialize($added_types), PLUGIN_ID);
		}

		return increment_views_counter($entity->guid);
	}

	return false;
}


/**
 * Return an array of entities ordered by the number of views
 * 
 * @param $options
 */
function get_entities_by_views_counter($options) {
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

	// Let the elgg_get_entities() function make do work for us :)
	$entities = elgg_get_entities($options);

	return $entities;
}

/**
 * Get the class in accordance to the plugin settings
 * 
 */
function get_views_counter_class() {
	$remove_css = elgg_get_plugin_setting('remove_css_class', PLUGIN_ID);
	if (!$remove_css || $remove_css == 'no') {
		$class = 'views_counter';
	}

	$float = elgg_get_plugin_setting('float_direction', 'views_counter');
	if ($float == 'none') {
		$float = '';
	}

	$class .= ' ' . $float;

	return $class;
}

/**
 * Get the last view time of a user for an entity
 * 
 * @param unknown_type $entity_guid
 * @param unknown_type $user_guid
 * @return boolean
 */
function get_last_view_time($entity_guid, $user_guid) {
	$entity = get_entity($entity_guid);

	if ($entity) {
		$user = get_entity($user_guid);
		if (!$user) {
			$user = elgg_get_logged_in_user_entity();
		}

		// Try to get the metadata views counter, if there is views counter then We may know which time the user made the last saw for this this entity
		$options = array(
			'guids' => array($entity_guid),
			'types' => array($entity->type),
			'annotation_names' => array('last_view_time'),
			'annotation_owner_guids' => array($user->guid)
		);

		if ($entity->getSubtype()) {
			$options['subtypes'] = array($entity->getSubtype());
		}

		$last_view_time = elgg_get_annotations($options);
		$last_view_time = $last_view_time[0];

		if ($last_view_time) {
			return $last_view_time->value;
		}
	}
	return false;
}

/**
 * Check if the user did see the last update for an entity based on
 * the last view time annotation and the updated time for the elgg entity
 * 
 * @param $entity_guid
 * @param $user_guid
 */
function did_see_last_update($entity_guid, $user_guid) {
	$entity = get_entity($entity_guid);

	if ($entity) {
		$user = get_entity($user_guid);
		if (!$user) {
			$user = elgg_get_logged_in_user_entity();
		}

		$last_view_time = get_last_view_time($entity->guid, $user->guid);
		$last_view_time = ($last_view_time) ? ($last_view_time) : 0;

		if ($entity->time_updated > $last_view_time) {
			return true;
		} else {
			return false;
		}
	}

	return null;
}
