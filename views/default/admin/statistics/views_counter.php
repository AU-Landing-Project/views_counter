<?php

namespace AU\ViewsCounter;

$entity_type = get_input('entity_type', 'user');
$title = elgg_echo('views_counter:admin_page');

// Shows the types pulldown that the admin may add a views counter system
echo elgg_view('views_counter/forms/types_pulldown', array('entity_type' => $entity_type));

// List the entities of the previous selected subtype
echo elgg_view('views_counter/list_entities', array('entity_type' => $entity_type));
