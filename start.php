<?php

namespace AU\ViewsCounter;

const PLUGIN_ID = 'views_counter';

require_once __DIR__ . '/lib/hooks.php';
require_once __DIR__ . '/lib/events.php';
require_once __DIR__ . '/lib/functions.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

/**
 * INIT
 */
function init() {
	// Try to add a views counter for the entities selected through the plugin settings
	set_views_counter();

	elgg_register_page_handler('views_counter', __NAMESPACE__ . '\\views_counter_page_handler');
	elgg_extend_view('css/elgg', 'css/views_counter');

	elgg_register_event_handler('pagesetup', 'system', __NAMESPACE__ . '\\pagesetup');

	elgg_register_action('views_counter/settings/save', __DIR__ . '/actions/views_counter/settings/save.php', 'admin');
	elgg_register_action('views_counter/list_entities', __DIR__ . '/actions/list_entities.php');
}

/**
 * To control the views_counter pages exhibition
 * 
 * @param $page
 */
function views_counter_page_handler($page) {
	if (isset($page[0])) {

		$return = FALSE;
		switch ($page[0]) {
			case 'list_entities':
				set_input('entity_type', $page[1]);
				if (include(elgg_get_plugins_path() . 'views_counter/admin_page.php')) {
					$return = TRUE;
				}
				break;

			case 'views_statistics':
				set_input('entity_guid', $page[1]);
				if (include(elgg_get_plugins_path() . 'views_counter/views_statistics.php')) {
					$return = TRUE;
				}
				break;
		}
	}

	return $return;
}
