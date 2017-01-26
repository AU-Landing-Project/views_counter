<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggAnnotation) {
	return;
}

$viewer = get_entity($item->owner_guid);
if (!$viewer) {
	echo elgg_echo('views_counter:not_loggedin');
} else {
	echo elgg_view('output/url', [
		'href' => $viewer->getURL(),
		'text' => $viewer->getDisplayName(),
	]);
}