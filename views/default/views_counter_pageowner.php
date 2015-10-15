<?php

namespace AU\ViewsCounter;

$view_override = elgg_get_config('views_counter_view_override');

$vars['views_counter_full_view_override'] = true;

if (!$vars['entity']) {
	// the entity isn't set yet...
	$vars['entity'] = elgg_get_page_owner_entity();
}

// Add the views counter to any elgg entity
echo elgg_view('views_counter/add', $vars);

// Shows the views counter number
echo elgg_view('views_counter/display_views_counter', $vars);
