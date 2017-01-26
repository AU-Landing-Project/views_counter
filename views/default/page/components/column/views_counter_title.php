<?php

$item = elgg_extract('item', $vars);
if ($item instanceof ElggAnnotation) {
	$entity = get_entity($item->entity_guid);
} else if ($item instanceof ElggEntity) {
	$entity = $item;
}

if ($entity) {
	echo elgg_view('output/url', [
		'href' => $entity->getURL(),
		'text' => $entity->getDisplayName(),
	]);
}
