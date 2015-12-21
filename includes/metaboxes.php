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
 *
 * @param string $type
 * @param object $user
 */
function wp_user_profiles_add_meta_boxes( $type = '', $user = null ) {

	// Bail if no user
	if ( empty( $user ) ) {
		return;
	}

	// Get types
	$types = wp_user_profiles_get_section_hooknames();

	// Bail if not the correct type
	if ( ! in_array( $type, $types, true ) ) {
		return;
	}

	// Do generic metaboxes
	do_action( 'wp_user_profiles_add_meta_boxes', $type, $user );
}

/**
 * Backwards compatibility for array-based profile sections
 *
 * @since 0.2.0
 *
 * @param string $type
 * @param object $user
 */
function wp_user_profiles_add_old_meta_boxes( $type = '', $user = null ) {

	// Get sections
	$sections = wp_user_profiles_sections();

	// Loop through sections
	foreach ( $sections as $section_id => $section ) {

		// Classes are handled internally
		if ( is_a( $section, 'WP_User_Profile_Section' ) ) {
			continue;
		}

		// Get types
		$types = wp_user_profiles_get_section_hooknames( $section_id );

		// Bail if not user metaboxes
		if ( ! in_array( $type, $types, true ) || ! current_user_can( $section->cap, $user->ID ) ) {
			continue;
		}

		// Do the metaboxes
		do_action( "wp_user_profiles_add_{$section_id}_meta_boxes", $type, $user );
	}
}

/**
 * Ensure the submit metabox is added to all profile pages
 *
 * @since 0.1.9
 *
 * @param string $type
 * @param object $user
 */
function wp_user_profiles_add_status_meta_box( $type = '', $user = null ) {

	// Register metaboxes for the user edit screen
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_status_metabox',
		$type,
		'side',
		'high',
		$user
	);
}
