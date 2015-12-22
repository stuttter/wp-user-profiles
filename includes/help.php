<?php

/**
 * User Profile Contextual Help
 * 
 * @package Plugins/Users/Profiles/Help
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add contextual help
 *
 * @since 0.2.0
 */
function wp_user_profiles_add_contextual_help() {

	// Static sidebar
	get_current_screen()->set_help_sidebar( 
		'<p>' . esc_html__( 'Some information may be displayed publicly on the site.', 'wp-user-profiles' ) . '</p>' .
		'<p>' . esc_html__( 'Always use a strong password, and never give your login information to anyone.', 'wp-user-profiles' ) . '</p>'
	);

	// Allow plugins to easily hook in
	do_action( 'wp_user_profiles_add_contextual_help' );
}
