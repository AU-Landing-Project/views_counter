<?php

$item = elgg_extract('item', $vars);
if ($item instanceof ElggAnnotation) {
	echo $item->entity_guid;
} else if ($item instanceof ElggEntity) {
	echo $item->guid;
}



