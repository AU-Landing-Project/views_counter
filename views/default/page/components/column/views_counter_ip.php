<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggAnnotation) {
	return;
}

$data = unserialize($item->value);

echo elgg_extract('ip_address', $data);
