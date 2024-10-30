<?php
/**
 * Plugin Name: Labinator Content Types Duplicator
 * Plugin URI: https://labinator.com/wordpress-marketplace/plugins/content-types-duplicator/
 * Description: Duplicate posts, pages, widgets, menus, media files, and any content types with one click. Copy or clone your content without limitations or restrictions.
 * Version: 1.1.3
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: Labinator
 * Author URI: https://labinator.com
 * Text Domain: lct-duplicator
 * Domain Path: /languages/
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) {
	exit;
}

// Version of the plugin
define('LCT_DUPLICATOR_CURRENT_VERSION', '1.1.3');

/**
 * Initialise the internationalisation domain
 */
function lct_duplicator_load_plugin_textdomain() {
	load_plugin_textdomain('lct-duplicator', FALSE, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'lct_duplicator_load_plugin_textdomain');

add_filter("plugin_action_links_" . plugin_basename(__FILE__), "lct_duplicator_plugin_actions", 10, 4);

function lct_duplicator_plugin_actions($actions, $plugin_file, $plugin_data, $context) {
	array_unshift($actions,
		sprintf('<a href="%s" aria-label="%s">%s</a>',
			menu_page_url('lct-duplicator', false),
			esc_attr__('Settings for Duplicate Post', 'lct-duplicator'),
			esc_html__("Settings", 'default')
		)
	);
	return $actions;
}

function lct_add_plugin_meta_links($links, $file) {

	if ($file == plugin_basename(__FILE__)) {
		$links[] = '<a href="https://labinator.com/wordpress-marketplace/" target="_blank">' . __('Labinator WordPress Marketplace', 'lct-duplicator') . '</a>';
	}

	return $links;
}

add_filter('plugin_row_meta', 'lct_add_plugin_meta_links', 10, 2);

require_once dirname(__FILE__) . '/lct-duplicator-common.php';

if (is_admin()) {
	require_once dirname(__FILE__) . '/lct-duplicator-admin.php';
}