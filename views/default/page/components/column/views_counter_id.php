<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggAnnotation) {
	return;
}

echo $item->entity_guid;

