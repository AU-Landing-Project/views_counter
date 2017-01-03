<?php

namespace AU\ViewsCounter;

$entity_type = get_input('entity_type', 'user');

// Shows the types pulldown that the admin may add a views counter system
echo elgg_view_form('views_counter/filter', [
	'action' => current_page_url(),
	'method' => 'GET',
	'disable_security' => true,
		], [
	'entity_type' => $entity_type,
]);

// List the entities of the previous selected subtype
echo elgg_view('views_counter/list_entities', ['entity_type' => $entity_type]);
