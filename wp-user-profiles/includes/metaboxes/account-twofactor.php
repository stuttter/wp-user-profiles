<?php

/**
 * User Profile Twofactor Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Twofactor
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add a new metabox dedicated to the 'Two-Factor' plugin to the account tab.
 *
 * @since  2.7.0
 * @param  string  $type
 * @param  WP_User $user
 *
 * @return void
 */
function wp_user_profiles_add_twofactor_metabox( $type, $user ) {
	add_meta_box(
		'twofactor',
		_x( 'Two-Factor Authentication', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_twofactor_metabox',
		$type,
		'normal',
		'core',
		$user
	);
}

/**
 * Render the 'two-factor'-plugin metabox for user account screen
 *
 * @since 2.7.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_twofactor_metabox( $user = null ) {

	// Before
	do_action( __FUNCTION__ . '_before', $user ); 

	// See 'Two-Factor' section of hooks.php
	// where this gets populated.

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
