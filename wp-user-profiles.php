<?php

/**
 * Plugin Name: WP User Profiles
 * Plugin URI:  https://wordpress.org/plugins/wp-user-profiles/
 * Description: A sophisticated way to edit users in WordPress
 * Author:      John James Jacoby
 * Version:     0.1.1
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPL v2 or later
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

	// Metaboxes
	require $plugin_path . 'metaboxes/about.php';
	require $plugin_path . 'metaboxes/capabilities.php';
	require $plugin_path . 'metaboxes/color-scheme.php';
	require $plugin_path . 'metaboxes/contact.php';
	require $plugin_path . 'metaboxes/email.php';
	require $plugin_path . 'metaboxes/name.php';
	require $plugin_path . 'metaboxes/password.php';
	require $plugin_path . 'metaboxes/personal-options.php';
	require $plugin_path . 'metaboxes/roles.php';
	require $plugin_path . 'metaboxes/sessions.php';
	require $plugin_path . 'metaboxes/status.php';

	// Required files
	require $plugin_path . 'includes/functions.php';
	require $plugin_path . 'includes/admin.php';
	require $plugin_path . 'includes/capabilities.php';
	require $plugin_path . 'includes/metaboxes.php';
	require $plugin_path . 'includes/hooks.php';
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
	return 201510150001;
}
