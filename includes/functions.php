<?php

/**
 * User Profile Functions
 * 
 * @package Plugins/Users/Profiles/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the file all menus will use as their parent
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_user_profiles_get_file() {

	// Default to users.php
	$file = 'users.php';

	// Maybe override to profile.php
	if ( is_user_admin() || ! current_user_can( 'list_users' ) ) {
		$file = 'profile.php';
	}

	// Filter & return
	return apply_filters( 'wp_user_profiles_get_file', $file );
}

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
 * @param  array $args
 *
 * @return array
 */
function wp_user_profiles_sections( $args = array() ) {

	// Sanity check for malformed global
	$r = ! empty( $GLOBALS['wp_user_profile_sections'] )
		? wp_parse_args( $args, $GLOBALS['wp_user_profile_sections'] )
		: wp_parse_args( $args, array() );

	// Parse arguments
	$sections = apply_filters( 'wp_user_profiles_sections', $r, $args );

	// Fix some common section issues
	foreach ( $sections as $section_id => $section ) {

		// Backwards compatibility for sections as arrays
		if ( is_array( $section ) ) {
			$sections[ $section_id ] = (object) $section;
		}

		// Backwards compatibility for sections without IDs
		if ( empty( $sections[ $section_id ]->id ) ) {
			$sections[ $section_id ]->id = $section_id;
		}
	}

	// Sort
	usort( $sections, 'wp_user_profiles_sort_sections' );

	// Return sections
	return $sections;
}

/**
 * Sort sections by order
 *
 * @since 0.2.0
 *
 * @param array $hip
 * @param array $hop
 * @return type
 */
function wp_user_profiles_sort_sections( $hip, $hop ) {
	return ( $hip->order - $hop->order );
}

/**
 * Get profile section slugs
 *
 * @since 0.1.7
 */
function wp_user_profiles_get_section_hooknames( $section = '' ) {

	// What slugs are we looking for
	$sections = ! empty( $section )
		? array( $section )
		: wp_list_pluck( wp_user_profiles_sections(), 'slug' );

	// Get file
	$hooks = array();
	$file  = wp_user_profiles_get_file();

	// Generate hooknames
	foreach ( $sections as $slug ) {
		$hookname = get_plugin_page_hookname( $slug, $file );
		$hooks[]  = $hookname;
	}

	// Network & user admin corrections
	array_walk( $hooks, '_wp_user_profiles_walk_section_hooknames' );

	return $hooks;
}

/**
 * Walk array and add maybe add network or user suffix
 *
 * @since 0.1.7
 *
 * @param string $value
 * @param string $key
 */
function _wp_user_profiles_walk_section_hooknames( &$value = '' ) {
	if ( is_network_admin() && substr( $value, -8 ) !== '-network' ) {
		$value .= '-network';
	} elseif ( is_user_admin() && substr( $value, -5 ) != '-user' ) {
		$value .= '-user';
	}
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

	$file = wp_user_profiles_get_file();

	// User admin (multisite only)
	if ( is_user_admin() ) {
		$url = user_admin_url( $file, $scheme );

	// Network admin editing
	} elseif ( is_network_admin() ) {
		$url = network_admin_url( $file, $scheme );

	// Fallback dashboard
	} else {
		$url = get_dashboard_url( $user_id, $file, $scheme );
	}

	// Add user ID to args array for other users
	if ( ! empty( $user_id ) && ( $user_id !== get_current_user_id() ) ) {
		$args['user_id'] = $user_id;
	}

	// Add query args
	$url = add_query_arg( $args, $url );

	// Filter and return
	return apply_filters( 'wp_user_profiles_get_admin_area_url', $url, $user_id, $scheme, $args );
}

function wp_user_profiles_get_edit_user_url( $user_id = 0 ) {
	return '';
}

/**
 * Save the user when they click "Update"
 *
 * @since 0.1.0
 */
function wp_user_profiles_save_user() {

	// Bail if not updating a user
	if ( empty( $_POST['user_id'] ) || empty( $_POST['action'] ) ) {
		return;
	}

	// Bail if not updating a user
	if ( 'update' !== $_POST['action'] ) {
		return;
	}

	// Set the user ID
	$user_id = (int) $_POST['user_id'];

	// Referring?
	if ( ! empty( $_REQUEST['wp_http_referer'] ) ) {
		$wp_http_referer = $_REQUEST['wp_http_referer'];
	} else {
		$wp_http_referer = false;
	}

	// Setup constant for backpat
	define( 'IS_PROFILE_PAGE', get_current_user_id() === $user_id );

	// Fire WordPress core actions
	if ( IS_PROFILE_PAGE ) {
		do_action( 'personal_options_update', $user_id );
	} else {
		do_action( 'edit_user_profile_update', $user_id );
	}

	// Get the userdata to compare it to
	$user = get_userdata( $user_id );

	// Do actions & return errors
	$errors = apply_filters( 'wp_user_profiles_save', $user );

	// Grant or revoke super admin status if requested.
	if ( is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) && ! isset( $GLOBALS['super_admins'] ) && empty( $_POST['super_admin'] ) == is_super_admin( $user_id ) ) {
		empty( $_POST['super_admin'] )
			? revoke_super_admin( $user_id )
			: grant_super_admin( $user_id );
	}

	// No errors
	if ( ! is_wp_error( $errors ) ) {
		$redirect = add_query_arg( 'updated', true );

		if ( ! empty( $wp_http_referer ) ) {
			$redirect = add_query_arg( 'wp_http_referer', urlencode( $wp_http_referer ), $redirect );
		}

		wp_redirect( $redirect );

		exit;

	// Errors
	} else {
		wp_die( $errors );
	}
}

/**
 * Add a notice when a profile is updated
 *
 * @since 0.1.0
 *
 * @param  mixed $notice
 * @return array
 */
function wp_user_profiles_save_user_notices() {

	// Bail
	if ( empty( $_GET['action'] ) || ( 'update' !== $_GET['action'] ) ) {
		return;
	}

	// Return the dismissible notice
	return array(
		'message' => esc_html__( 'User updated.', 'wp-user-profiles' ),
		'classes' => array(
			'updated',
			'notice',
			'notice-success',
			'is-dismissible'
		)
	);
}
