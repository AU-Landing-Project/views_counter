<?php

namespace AU\ViewsCounter;

/**
 * Add a view counter to any elgg entity
 * 
 * @uses $vars['entity'] An entity which the views counter will be added to
 * @uses $vars['entity_guid'] An entity guid to override $vars['entity']
 */
$entity_guid = elgg_extract('entity_guid', $vars);
if ($entity_guid) {
	$entity = get_entity($entity_guid);
} else {
	$entity = elgg_extract('entity', $vars);
}

if (!elgg_instanceof($entity)) {
	return;
}

if ($entity->type == 'object') {
	$subtype = $entity->getSubtype();
} else {
	$subtype = $entity->type;
}

$added_types = unserialize(elgg_get_plugin_setting('add_views_counter', PLUGIN_ID));
$removed_types = unserialize(elgg_get_plugin_setting('remove_views_counter', PLUGIN_ID));

if (!in_array($subtype, $added_types) || in_array($subtype, $removed_types)) {
	return;
}

$full_view = elgg_extract('full_view', $vars);
$full_view_ignore = elgg_extract('views_counter_full_view_override');

if (!$full_view && !$full_view_ignore) {
	return;
}

add_views_counter($entity->guid);
