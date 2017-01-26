<?php

namespace AU\ViewsCounter;

$entity_guid = elgg_extract('entity_guid', $vars);
if ($entity_guid) {
	$entity = get_entity($entity_guid);
} else {
	$entity = elgg_extract('entity', $vars);
}

if (!elgg_instanceof($entity) || !is_views_counter_enabled($entity)) {
	return;
}

$full_view = elgg_extract('full_view', $vars);
$full_view_ignore = elgg_extract('views_counter_full_view_override');

if (!$full_view && !$full_view_ignore) {
	return;
}

$display = elgg_get_plugin_setting('display_views_counter', PLUGIN_ID);
if (!$display) {
	return;
}

elgg_register_plugin_hook_handler('register', 'menu:entity', function($hook, $type, $return, $params) use ($entity) {

	$hook_entity = elgg_extract('entity', $params);

	if ($hook_entity->guid != $entity->guid) {
		return;
	}

	$text = elgg_echo('views_counter:views_count', [get_views_counter($entity->guid)]);
	
	$href = false;
	if (elgg_is_admin_logged_in()) {
		$href = "admin/views_counter/stats?guid=$entity->guid";
	}
	
	$return[] = \ElggMenuItem::factory([
		'name' => 'views_counter',
		'text' => $text,
		'href' => $href,
		'data-guid' => $entity->guid,
		'class' => 'views-counter',
		'priority' => 50,
	]);

	return $return;

}, 100);