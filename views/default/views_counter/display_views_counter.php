<?php

namespace AU\ViewsCounter;

$entity_guid = elgg_extract('entity_guid', $vars);
if ($entity_guid) {
	$entity = get_entity($entity_guid);
} else {
	$entity = elgg_extract('entity', $vars);
}

if (!elgg_instanceof($entity)) {
	return;
}

$full_view = elgg_extract('full_view', $vars);
$full_view_ignore = elgg_extract('views_counter_full_view_override');

if (!$full_view && !$full_view_ignore) {
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

if (elgg_get_config('views_counter_' . $entity->guid)) {
	return; // we've already rendered this once this page
}

elgg_set_config('views_counter_' . $entity->guid, true);

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

$content = get_views_counter($entity->guid) . ' ' . elgg_echo('views_counter:views');
if (elgg_is_admin_logged_in()) {
	$content = elgg_view('output/url', array(
		'text' => $content,
		'href' => 'admin/views_counter/stats?guid=' . $entity->guid
	));
}

echo '<span ' . elgg_format_attributes($span_attr) . '>' . $content . '</span>';

// Include the js code for views counter
//echo elgg_view('js/views_counter',$vars);
elgg_require_js('views_counter');
