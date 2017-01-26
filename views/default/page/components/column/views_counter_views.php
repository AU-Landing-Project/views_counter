<?php

$item = elgg_extract('item', $vars);
if ($item instanceof ElggAnnotation) {
	$entity = get_entity($item->entity_guid);
	$value = $item->value;

	if ($entity->type == 'object') {
		$subtype = $entity->getSubtype();
	} else {
		$subtype = $entity->type;
	}

	$logged_types = unserialize(elgg_get_plugin_setting('add_views_logger', 'views_counter'));
	$is_logged = in_array($subtype, $logged_types);

	if (!$is_logged) {
		echo $value;
		return;
	}

	echo elgg_view('output/url', [
		'href' => elgg_http_add_url_query_elements('admin/views_counter/stats', [
			'guid' => $entity->guid,
			'viewer_guid' => $item->owner_guid,
		]),
		'text' => $value,
	]);
} else if ($item instanceof ElggEntity) {
	$entity = $item;
	$value = AU\ViewsCounter\get_views_counter($entity->guid);

	echo elgg_view('output/url', [
		'href' => elgg_http_add_url_query_elements('admin/views_counter/stats', [
			'guid' => $entity->guid,
			'viewer_guid' => $item->owner_guid,
		]),
		'text' => $value,
	]);
}
