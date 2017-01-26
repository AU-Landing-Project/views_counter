<?php

/**
 * Add a view counter to any elgg entity
 *
 * @uses $vars['entity'] An entity which the views counter will be added to
 * @uses $vars['entity_guid'] An entity guid to override $vars['entity']
 */

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

add_views_counter($entity->guid);
