<?php

echo elgg_view_field([
	'#type' => 'fieldset',
	'align' => 'horizontal',
	'fields' => [
		[
			'#type' => 'views_counter/entity_types',
			'#label' => elgg_echo('views_counter:select_type'),
			'value' => elgg_extract('entity_type', $vars),
			'name' => 'entity_type',
		],
		[
			'#type' => 'submit',
			'value' => elgg_echo('views_counter:see_entities'),
		]
	],
]);