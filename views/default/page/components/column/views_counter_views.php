<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggAnnotation) {
	return;
}

$entity = get_entity($item->entity_guid);

if (!$entity) {
	echo $item->value;
	return;
}

if ($entity->type == 'object') {
	$subtype = $entity->getSubtype();
} else {
	$subtype = $entity->type;
}

$logged_types = unserialize(elgg_get_plugin_setting('add_views_logger', 'views_counter'));
$is_logged = in_array($subtype, $logged_types);

if (!$is_logged) {
	echo $item->value;
	return;
}

echo elgg_view('output/url', [
	'href' => elgg_http_add_url_query_elements(current_page_url(), [
		'viewer_guid' => $item->owner_guid,
	]),
	'text' => $item->value,
]);
