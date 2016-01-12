<?php

/**
 * Plugin Name: WP User Profiles
 * Plugin URI:  https://wordpress.org/plugins/wp-user-profiles/
 * Author:      John James Jacoby
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: A sophisticated way to edit users in WordPress
 * Version:     0.2.0
 * Text Domain: wp-user-profiles
 * Domain Path: /assets/lang/
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
	$plugin_path = plugin_dir_path( __FILE__ );

	// Sections
	require_once $plugin_path . 'includes/sections/base.php';
	require_once $plugin_path . 'includes/sections/profile.php';
	require_once $plugin_path . 'includes/sections/account.php';
	require_once $plugin_path . 'includes/sections/options.php';
	require_once $plugin_path . 'includes/sections/permissions.php';

	// Meta Boxes
	require_once $plugin_path . 'includes/metaboxes/all-status.php';
	require_once $plugin_path . 'includes/metaboxes/account-email.php';
	require_once $plugin_path . 'includes/metaboxes/account-password.php';
	require_once $plugin_path . 'includes/metaboxes/account-sessions.php';
	require_once $plugin_path . 'includes/metaboxes/options-color-scheme.php';
	require_once $plugin_path . 'includes/metaboxes/options-contact.php';
	require_once $plugin_path . 'includes/metaboxes/options-personal.php';
	require_once $plugin_path . 'includes/metaboxes/options-primary-site.php';
	require_once $plugin_path . 'includes/metaboxes/permissions-capabilities.php';
	require_once $plugin_path . 'includes/metaboxes/permissions-roles.php';	
	require_once $plugin_path . 'includes/metaboxes/profile-about.php';
	require_once $plugin_path . 'includes/metaboxes/profile-name.php';

	// Required Files
	require_once $plugin_path . 'includes/admin.php';
	require_once $plugin_path . 'includes/capabilities.php';
	require_once $plugin_path . 'includes/dependencies.php';
	require_once $plugin_path . 'includes/functions.php';
	require_once $plugin_path . 'includes/help.php';
	require_once $plugin_path . 'includes/metaboxes.php';
	require_once $plugin_path . 'includes/screen-options.php';
	require_once $plugin_path . 'includes/sections.php';
	require_once $plugin_path . 'includes/status.php';
	require_once $plugin_path . 'includes/hooks.php';
}
add_action( 'plugins_loaded', '_wp_user_profiles' );

/**
 * Return the plugin's URL
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_user_profiles_get_plugin_url() {
	return plugin_dir_url( __FILE__ );
}

/**
 * Return the asset version
 *
 * @since 0.1.0
 *
 * @return int
 */
function wp_user_profiles_get_asset_version() {
	return 201512230001;
}
