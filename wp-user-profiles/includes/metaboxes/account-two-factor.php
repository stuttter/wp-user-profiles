<?php

/**
 * User Profile Two-factor Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/TwoFactor
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the 'two-factor'-plugin metabox for user account screen
 *
 * @since 2.7.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_two_factor_metabox( $user = null ) {

	// Before
	do_action( __FUNCTION__ . '_before', $user ); 

	// Call the method from the Two-Factor plugin
	call_user_func( array( 'Two_Factor_Core', 'user_two_factor_options' ), $user );

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
