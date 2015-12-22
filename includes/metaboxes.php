<?php

/**
 * User Profile Metaboxes
 *
 * @package Plugins/Users/Profiles/Metaboxes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add all of the User Profile metaboxes
 *
 * @since 0.2.0
 */
function wp_user_profiles_add_meta_boxes() {

	// Get the current user ID
	$current_user_id = get_current_user_id();

	// Get the user ID being edited
	$user_id = ! empty( $_GET['user_id'] )
		? (int) $_GET['user_id']
		: (int) $current_user_id;

	// Maybe set constant if editing oneself
	if ( ! defined( 'IS_PROFILE_PAGE' ) ) {
		define( 'IS_PROFILE_PAGE', ( $user_id === $current_user_id ) );
	}

	// Get the user being edited & bail if user does not exist
	$user = get_userdata( $user_id );
	if ( empty( $user ) ) {
		wp_die( esc_html__( 'Invalid user ID.', 'wp-user-profiles' ) );
	}

	// Adjust the type for user/network dashboards
	$hook = $GLOBALS['page_hook'];
	_wp_user_profiles_walk_section_hooknames( $hook );

	// Get possible hooknames
	$hooks = wp_user_profiles_get_section_hooknames();

	// Bail if not the correct type
	if ( ! in_array( $hook, $hooks, true ) ) {
		return;
	}

	// Do generic metaboxes
	do_action( 'wp_user_profiles_add_meta_boxes', $hook, $user );
}

/**
 * Ensure the submit metabox is added to all profile pages
 *
 * @since 0.1.9
 *
 * @param  string  $hook
 * @param  WP_User $user
 */
function wp_user_profiles_add_status_meta_box( $hook = '', $user = null ) {

	// Register the "Status" side meta box
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_status_metabox',
		$hook,
		'side',
		'high',
		$user
	);
}
