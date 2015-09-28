<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Conditionally filter the URL used to edit a user
 *
 * This function does some primitive routing for theme-side user profile editing,
 * but since this is largely in flux and likely related to several other plugins,
 * themes, and other factors, we'll be tweaking this a bit in the future.
 *
 * @since 0.1.0
 *
 * @param  string  $url
 * @param  int     $user_id
 * @param  string  $scheme
 *
 * @return string
 */
function wp_user_profiles_edit_user_url_filter( $url = '', $user_id = 0, $scheme = '' ) {

	// Get user ID
	$user_id = ! empty( $user_id )
		? (int) $user_id
		: get_current_user_id();

	// Admin area editing
	if ( is_admin() ) {
		$url = wp_user_profiles_get_admin_area_url( $user_id, $scheme );

	// Theme side editing
	} else {
		$url = wp_user_profiles_get_edit_user_url( $user_id );
	}

	return add_query_arg( array( 'page' => 'profile' ), $url );
}

/**
 * Return an array of profile sections
 *
 * @since 0.1.0
 *
 * @param   array  $args
 *
 * @return  array
 */
function wp_user_profiles_sections( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(

		// Profile
		'profile' => array(
			'slug' => 'profile',
			'name' => esc_html__( 'Profile', 'wp-user-profiles' ),
			'cap'  => 'edit_user'
		),

		// Acount
		'account' => array(
			'slug' => 'account',
			'name' => esc_html__( 'Account', 'wp-user-profiles' ),
			'cap'  => 'edit_user'
		),

		// Options
		'options' => array(
			'slug' => 'options',
			'name' => esc_html__( 'Options', 'wp-user-profiles' ),
			'cap'  => 'edit_user'
		),

		// Roles
		'roles' => array(
			'slug' => 'roles',
			'name' => esc_html__( 'Permissions', 'wp-user-profiles' ),
			'cap'  => 'edit_user'
		)
	) );

	// Filter & return
	return apply_filters( 'wp_user_profiles_sections', $r, $args );
}

/**
 * Return the admin area URL for a user
 *
 * @since 0.1.0
 *
 * @param  int     $user_id
 * @param  string  $scheme
 * @param  array   $args
 *
 * @return string
 */
function wp_user_profiles_get_admin_area_url( $user_id = 0, $scheme = '', $args = array() ) {

	// User admin (multisite only)
	if ( is_user_admin() ) {
		$url = user_admin_url( 'admin.php', $scheme );

	// Network admin editing
	} elseif ( is_network_admin() ) {
		$url = network_admin_url( 'admin.php', $scheme );

	// Fallback dashboard
	} else {
		$url = get_dashboard_url( $user_id, 'admin.php', $scheme );
	}

	// Add query args
	$url = add_query_arg( $args, $url );

	// Filter and return
	return apply_filters( 'wp_user_profiles_get_admin_area_url', $url, $user_id, $scheme, $args );
}

function wp_user_profiles_get_edit_user_url( $user_id = 0 ) {
	return '';
}