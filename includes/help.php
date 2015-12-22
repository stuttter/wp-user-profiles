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
		'<p>' . esc_html__( 'Edit all of the various account details here.', 'wp-user-profiles' ) . '</p>'
	);

	do_action( 'wp_user_profiles_add_contextual_help' );
}
