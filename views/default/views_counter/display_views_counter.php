<?php

namespace AU\ViewsCounter;

$entity_guid = ($vars['entity']) ? ($vars['entity']->guid) : $vars['entity_guid'];
$entity = get_entity($entity_guid);

if (!$entity || (!$vars['full_view'] && !$vars['views_counter_full_view_override'])) {
	return;
}

if (elgg_get_config('views_counter_' . $entity_guid)) {
	return; // we've already rendered this once this page
}
elgg_set_config('views_counter_' . $entity_guid, true);

$target = elgg_get_plugin_setting('views_counter_container_id', PLUGIN_ID);
$display = elgg_get_plugin_setting('display_views_counter', PLUGIN_ID);


$classes = array('views-counter-container');
$classes[] = get_views_counter_class();
if ($target || ($display == 'no')) {
	$classes[] = 'hidden';
}
$classes = array_unique($classes);
$classes = array_map('trim', $classes);

$span_attr = array(
	'class' => implode(' ', $classes),
	'data-guid' => $entity->guid,
	'data-target' => $target
);

$content = get_views_counter($entity_guid) . ' ' . elgg_echo('views_counter:views');
if (elgg_is_admin_logged_in()) {
	$content = elgg_view('output/url', array(
		'text' => $content,
		'href' => 'admin/views_counter/stats?guid=' . $entity_guid
	));
}

echo '<span ' . elgg_format_attributes($span_attr) . '>' . $content . '</span>';

// Include the js code for views counter
//echo elgg_view('js/views_counter',$vars);
elgg_require_js('views_counter');
