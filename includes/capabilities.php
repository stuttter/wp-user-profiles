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
			if ( defined( 'IS_PROFILE_PAGE' ) && ( true === IS_PROFILE_PAGE ) ) {
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
 * Prevent access to `profile.php`
 *
 * @since 0.2.0
 *
 * @param type $redirect_to
 * @param type $requested_redirect_to
 * @param type $user
 */
function wp_user_profiles_old_profile_redirect() {
	wp_safe_redirect( get_dashboard_url() );
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
