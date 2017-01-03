<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggAnnotation) {
	return;
}


echo date('M j, Y H:i', AU\ViewsCounter\get_last_view_time($item->entity_guid, $item->owner_guid));
