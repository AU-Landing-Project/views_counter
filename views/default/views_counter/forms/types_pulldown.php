<?php

namespace AU\ViewsCounter;

$action = current_page_url();
$form_body = elgg_view('views_counter/entity_types_pulldown', $vars);
$form_body .= ' ';
$form_body .= elgg_view('input/submit', array('value' => elgg_echo('views_counter:see_entities')));
echo elgg_view('input/form', array(
	'action' => $action,
	'body' => $form_body,
	'method' => 'get',
	'disable_security' => true
));
