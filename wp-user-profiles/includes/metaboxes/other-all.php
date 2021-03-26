<?php

/**
 * User Profile Other Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Other
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the other-all metabox for other screen
 *
 * @since 0.3.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_other_metabox( $user = null ) {

	// Start an output buffer
	ob_start();

	// Get the correct action
	$action = wp_is_profile_page()
		? 'show_user_profile'
		: 'edit_user_profile';

	// Before
	do_action( __FUNCTION__ . '_before', $user );

	// This action documented in wp-admin/user-edit.php
	do_action( $action, $user );

	// After
	do_action( __FUNCTION__ . '_after', $user );

	// Get (and clean) the current output buffer
	$output = ob_get_clean();

	// Hooks are working
	if ( ! empty( $output ) ) {
		echo $output;

	// Hooks are doing weird things
	} else {
		echo wpautop( esc_html__( 'A plugin attempted to show something here, but then failed to do so.', 'wp-user-profiles' ) );
	}
}
