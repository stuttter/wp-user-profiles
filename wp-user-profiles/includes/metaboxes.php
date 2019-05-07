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

	// Try to get the user being edited
	$user = wp_user_profiles_get_user_to_edit();

	// Maybe die if user cannot be edited
	wp_user_profiles_current_user_can_edit( $user->ID );

	// Get the 
	$hook = isset( $GLOBALS['page_hook'] )
		? sanitize_key( $GLOBALS['page_hook'] )
		: null;

	// Adjust the hook for user/network dashboards and pass into the action
	wp_user_profiles_walk_section_hooknames( $hook );

	// Do generic metaboxes
	do_action( 'wp_user_profiles_add_meta_boxes', $hook, array(
		'user' => $user
	) );
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
