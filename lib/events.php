<?php

namespace AU\ViewsCounter;

/**
 * Set some views_counter links on elgg system
 * todo - clean up
 */
function pagesetup() {
	if (elgg_is_admin_logged_in() && (elgg_get_context() == 'admin') || (elgg_get_context() == 'views_counter')) {
		$item = new ElggMenuItem('views_counter_admin', elgg_echo('views_counter:admin_page'), elgg_get_site_url() . 'views_counter/list_entities/user');
		elgg_register_menu_item('page', $item);
	}
}

