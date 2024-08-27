<?php

/**
 * Plugin Name:       WP User Profiles
 * Description:       Upgrade your WordPress admin user profile experience
 * Plugin URI:        https://wordpress.org/plugins/wp-user-profiles/
 * Author:            Triple J Software, Inc.
 * Author URI:        https://jjj.software
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-user-profiles
 * Domain Path:       /wp-user-profiles/includes/languages
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Tested up to:      6.6
 * Version:           2.6.2
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

	// Core Files
	$sources = array(
		// Sections
		'includes/sections/base.php',
		'includes/sections/profile.php',
		'includes/sections/account.php',
		'includes/sections/options.php',
		'includes/sections/other.php',
		'includes/sections/permissions.php',
		'includes/sections/sites.php',

		// Meta Boxes
		'includes/metaboxes/all-status.php',
		'includes/metaboxes/account-email.php',
		'includes/metaboxes/account-language.php',
		'includes/metaboxes/account-password.php',
		'includes/metaboxes/account-applications.php',
		'includes/metaboxes/account-sessions.php',
		'includes/metaboxes/account-two-factor.php',
		'includes/metaboxes/options-color-scheme.php',
		'includes/metaboxes/options-contact.php',
		'includes/metaboxes/options-personal.php',
		'includes/metaboxes/other-all.php',
		'includes/metaboxes/permissions-capabilities.php',
		'includes/metaboxes/permissions-roles.php',
		'includes/metaboxes/profile-about.php',
		'includes/metaboxes/profile-name.php',
		'includes/metaboxes/sites-list.php',
		'includes/metaboxes/sites-primary.php',

		// Required Files
		'includes/admin.php',
		'includes/capabilities.php',
		'includes/dependencies.php',
		'includes/common.php',
		'includes/help.php',
		'includes/metaboxes.php',
		'includes/screen-options.php',
		'includes/sections.php',
		'includes/sponsor.php',
		'includes/status.php',
		'includes/hooks.php'
	);

	$files = array();
	foreach( $sources as $key => $source ) {
		$files[ $key ] = $plugin_path . $source;
	}
	// Allow for filtering of the Core Files
	$core_files = apply_filters( 'wp_user_profiles_core_files', $files, $sources );

	foreach( $core_files as $file ) {
		require_once $file;
	}

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
		: 202105290001;
}
