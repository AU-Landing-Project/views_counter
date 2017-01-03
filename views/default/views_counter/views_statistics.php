<?php

/**
 * Displays entity view statistics
 *
 * @uses $vars['entity'] Entity
 */

namespace AU\ViewsCounter;

$entity = elgg_extract('entity', $vars);
if (!$entity) {
	forward('', '404');
}

$viewer_guid = elgg_extract('viewer_guid', $vars);

if ($viewer_guid) {
	echo elgg_list_entities([
		'guids' => $entity->guid,
		'annotation_names' => 'views_counter_log',
		'annotation_owner_guids' => $viewer_guid,
		'list_type' => 'table',
		'list_class' => 'elgg-table-alt',
		'columns' => [
			elgg()->table_columns->views_counter_id(),
			elgg()->table_columns->views_counter_title(),
			elgg()->table_columns->views_counter_viewer(),
			elgg()->table_columns->time_created(elgg_echo('views_counter:log_time')),
			elgg()->table_columns->views_counter_ip(),
		],
		'no_results' => elgg_echo('views_counter:stats:none'),
			], 'elgg_get_annotations');
} else {
	echo elgg_list_entities([
		'guids' => $entity->guid,
		'annotation_names' => 'views_counter',
		'list_type' => 'table',
		'list_class' => 'elgg-table-alt',
		'columns' => [
			elgg()->table_columns->views_counter_id(),
			elgg()->table_columns->views_counter_title(),
			elgg()->table_columns->views_counter_viewer(),
			elgg()->table_columns->views_counter_views(),
			elgg()->table_columns->views_counter_first_view(),
			elgg()->table_columns->views_counter_last_view(),
		],
		'order_by' => '(value + 0) desc',
		'no_results' => elgg_echo('views_counter:stats:none'),
			], 'elgg_get_annotations');
}