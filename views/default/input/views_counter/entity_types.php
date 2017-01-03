<?php

namespace AU\ViewsCounter;

$valid_types = get_valid_types_for_views_counter();
foreach ($valid_types as $type) {
	if (in_array($type, ['user', 'group'])) {
		$options_values[$type] = elgg_echo("item:$type");
	} else {
		$options_values[$type] = elgg_echo("item:object:$type");
	}
}

$vars['options_values'] = $options_values;

echo elgg_view('input/select', $vars);