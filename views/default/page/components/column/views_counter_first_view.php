<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggAnnotation) {
	return;
}

echo date('M j, Y H:i', $item->time_created);
