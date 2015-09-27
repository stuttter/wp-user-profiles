<?php

/**
 * Plugin Name: WP User Profiles
 * Plugin URI:  https://wordpress.org/plugins/wp-user-profiles/
 * Description: User profiles, the way they should be
 * Author:      John James Jacoby
 * Version:     0.1.0
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
	return 201509260001;
}
