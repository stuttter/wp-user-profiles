<?php

/**
 * User Profile Capabilities
 *
 * @package Plugins/Users/Profiles/Capabilities
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Remap meta capabilities for `edit_user` glitch.
 *
 * This function exists because `add_submenu_item()` calls current_user_can()
 * without any additional arguments, therefor checks for `edit_user` will fail.
 *
 * @since 0.2.0
 *
 * @param string $hook
 */
function wp_user_profiles_map_meta_cap( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	// Which caps are we checking
	switch ( $cap ) {
		case 'edit_profile' :

			// Not authorized
			$authed = false;

			// Authed when looking at own profile
			if ( wp_is_profile_page() ) {
				$authed = true;

			// Other cases
			} else {

				// Compare current user ID
				$current_user_id = get_current_user_id();

				// User matches current user
				if ( $user_id === $current_user_id ) {
					$authed = true;

				// Passed user matches current user
				} elseif ( isset( $args[0] ) && ( $args[0] === $current_user_id ) ) {
					$authed = true;
				}
			}

			// Somehow authed
			if ( true === $authed ) {
				$caps = array( 'exist' );
			}

			break;
	}

	return $caps;
}

/**
 * Check that the current user can actually edit the user being requested
 *
 * @since 2.0.0
 *
 * @param int $user_id
 *
 * @return void Will wp_die() with traditional WordPress messaging on failure
 */
function wp_user_profiles_current_user_can_edit( $user_id = 0 ) {

	// Bail if user does not exist
	$user = get_userdata( $user_id );
	if ( empty( $user ) ) {
		wp_die( esc_html__( 'Invalid user ID.', 'wp-user-profiles' ) );
	}

	// Can the current user edit the requested user ID?
	if (

		// Allow administrators on Multisite to edit every user?
		(
			is_multisite()
				&& ! current_user_can( 'manage_network_users' )
				&& ( $user->ID !== get_current_user_id() )
				&& ! apply_filters( 'enable_edit_any_user_configuration', true )
		)

		// OR
		||

		// Explicitly check the current user against the requested one
		(
			! current_user_can( 'edit_user', $user->ID )
		)
	) {
		wp_die( esc_html__( 'Sorry, you are not allowed to edit this user.', 'wp-user-profiles' ) );
	}
}

/**
 * Prevent access to `profile.php`
 *
 * @since 0.2.0
 *
 * @param type $redirect_to
 * @param type $requested_redirect_to
 * @param type $user
 */
function wp_user_profiles_old_profile_redirect() {

	// Get the redirect URL
	$url = get_edit_profile_url( get_current_user_id() );

	// Do the redirect
	wp_safe_redirect( $url );
	exit;
}

/**
 * Prevent access to `user-edit.php`
 *
 * @since 0.2.0
 *
 * @param type $redirect_to
 * @param type $requested_redirect_to
 * @param type $user
 */
function wp_user_profiles_old_user_edit_redirect() {

	// Get the user ID
    $user_id = ! empty( $_REQUEST['user_id'] )
		? absint( $_REQUEST['user_id'] )
		: get_current_user_id();

	// Get the redirect URL
    $user_edit_url = add_query_arg( array(
		'page' => 'profile'
	), wp_user_profiles_get_admin_area_url( $user_id ) );

	// Do the redirect
    wp_safe_redirect( $user_edit_url );
    exit;
}

/**
 * Does a user account have support for a certain feature?
 *
 * @since 2.4.0
 * @param string $thing
 * @param int    $user_id
 * @return bool
 */
function wp_user_profiles_user_supports( $thing = '', $user_id = 0 ) {

	// Default return value
	$retval = false;

	// Use the first in an array
	if ( is_array( $user_id ) ) {
		$user_id = reset( $user_id );
	}

	// Use the ID of an object
	if ( is_object( $user_id ) ) {
		$user_id = $user_id->ID;
	}

	// Cast to absolute integer
	$user_id = absint( $user_id );

	// What thing?
	switch ( $thing ) {

		// Application Passwords in WordPress 5.6
		case 'application-passwords' :
			if ( function_exists( 'wp_is_application_passwords_available_for_user' ) && wp_is_application_passwords_available_for_user( $user_id ) ) {
				$retval = true;
			}
			break;
	}

	// Filter & return
	return apply_filters( '', $retval, $thing, $user_id );
}
