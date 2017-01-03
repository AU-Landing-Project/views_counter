<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggAnnotation) {
	return;
}

$entity = get_entity($item->entity_guid);
if (!$entity) {
	echo '';
} else {
	echo elgg_view('output/url', [
		'href' => $entity->getURL(),
		'text' => $entity->getDisplayName(),
	]);
}