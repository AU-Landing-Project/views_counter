<?php

$entity = get_entity(get_input('guid'));

// Shows the views statistics for an elgg entity
echo elgg_view('views_counter/views_statistics', array(
	'entity' => $entity
));
