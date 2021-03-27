<?php

/**
 * Plugin Name:       WP User Profiles
 * Plugin URI:        https://wordpress.org/plugins/wp-user-profiles/
 * Author:            John James Jacoby
 * Author URI:        https://profiles.wordpress.org/johnjamesjacoby/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Description:       A sophisticated way to edit users in WordPress
 * Version:     2.6.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Text Domain:       wp-user-profiles
 * Domain Path:       /wp-user-profiles/includes/languages
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Include the User Profiles files
 *
 * @since 0.1.0
 */
function _wp_user_profiles() {

	// Get the plugin path
	$plugin_path = plugin_dir_path( __FILE__ ) . 'wp-user-profiles/';

	// Sections
	require_once $plugin_path . 'includes/sections/base.php';
	require_once $plugin_path . 'includes/sections/profile.php';
	require_once $plugin_path . 'includes/sections/account.php';
	require_once $plugin_path . 'includes/sections/options.php';
	require_once $plugin_path . 'includes/sections/other.php';
	require_once $plugin_path . 'includes/sections/permissions.php';
	require_once $plugin_path . 'includes/sections/sites.php';

	// Meta Boxes
	require_once $plugin_path . 'includes/metaboxes/all-status.php';
	require_once $plugin_path . 'includes/metaboxes/account-email.php';
	require_once $plugin_path . 'includes/metaboxes/account-language.php';
	require_once $plugin_path . 'includes/metaboxes/account-password.php';
	require_once $plugin_path . 'includes/metaboxes/account-applications.php';
	require_once $plugin_path . 'includes/metaboxes/account-sessions.php';
	require_once $plugin_path . 'includes/metaboxes/options-color-scheme.php';
	require_once $plugin_path . 'includes/metaboxes/options-contact.php';
	require_once $plugin_path . 'includes/metaboxes/options-personal.php';
	require_once $plugin_path . 'includes/metaboxes/other-all.php';
	require_once $plugin_path . 'includes/metaboxes/permissions-capabilities.php';
	require_once $plugin_path . 'includes/metaboxes/permissions-roles.php';
	require_once $plugin_path . 'includes/metaboxes/profile-about.php';
	require_once $plugin_path . 'includes/metaboxes/profile-name.php';
	require_once $plugin_path . 'includes/metaboxes/sites-list.php';
	require_once $plugin_path . 'includes/metaboxes/sites-primary.php';

	// Required Files
	require_once $plugin_path . 'includes/admin.php';
	require_once $plugin_path . 'includes/capabilities.php';
	require_once $plugin_path . 'includes/dependencies.php';
	require_once $plugin_path . 'includes/common.php';
	require_once $plugin_path . 'includes/help.php';
	require_once $plugin_path . 'includes/metaboxes.php';
	require_once $plugin_path . 'includes/screen-options.php';
	require_once $plugin_path . 'includes/sections.php';
	require_once $plugin_path . 'includes/status.php';
	require_once $plugin_path . 'includes/hooks.php';

	// Load translations
	load_plugin_textdomain( 'wp-user-profiles' );
}
add_action( 'plugins_loaded', '_wp_user_profiles' );

/**
 * Return the plugin URL
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_user_profiles_get_plugin_url() {
	return plugin_dir_url( __FILE__ ) . 'wp-user-profiles/';
}

/**
 * Return the asset version
 *
 * @since 0.1.0
 *
 * @return int
 */
function wp_user_profiles_get_asset_version() {
	return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG
		? time()
		: 202103260001;
}
