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

	// Fire legacy WordPress actions
	wp_is_profile_page()
		? do_action( 'show_user_profile', $user )
		: do_action( 'edit_user_profile', $user );
}
