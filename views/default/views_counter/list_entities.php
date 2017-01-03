<?php

/**
 * Display a list of entities ordered by views count
 *
 * @uses $vars['entity_type'] Entity type or object subtype
 */

namespace AU\ViewsCounter;

$options = [
	'limit' => 40,
	'list_type' => 'table',
	'list_class' => 'elgg-table-alt',
	'columns' => [
		elgg()->table_columns->views_counter_id(),
		elgg()->table_columns->views_counter_title(),
		elgg()->table_columns->views_counter_views(),
	],
	'no_results' => elgg_echo('views_counter:list_entities:no_results'),
];

$entity_type = elgg_extract('entity_type', $vars, 'user');
switch ($entity_type) {
	case 'user' :
	case 'group' :
		$options['types'] = $entity_type;
		break;
	default :
		$options['types'] = 'object';
		$options['subtypes'] = $entity_type;
		break;
}

echo elgg_list_entities($options, __NAMESPACE__ . '\\get_entities_by_views_counter');
