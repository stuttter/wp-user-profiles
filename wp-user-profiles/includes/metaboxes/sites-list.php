<?php

/**
 * User Profile Sites Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Sites
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the sites metabox for user profile screen
 *
 * @since 1.0.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_sites_metabox( $user = null ) {

	// Get sites for user
	$sites = get_blogs_of_user( $user->ID, true );

	// Temporary
	$GLOBALS['wp_user_profiles_site_in'] = array_keys( $sites );
	$screen = get_current_screen();
	set_current_screen( 'network-sites' );
	add_filter( 'ms_sites_list_table_query_args', 'wp_user_profiles_filter_sites_table_query_args' );

	// Get the list table & items
	$wp_list_table = _get_list_table( 'WP_MS_Sites_List_Table' );
	$wp_list_table->prepare_items();
	$wp_list_table->_actions = false;

	// Reset
	remove_filter( 'ms_sites_list_table_query_args', 'wp_user_profiles_filter_sites_table_query_args' );
	unset( $GLOBALS['wp_user_profiles_site_in'] );
	set_current_screen( $screen->id );

	// Start
	ob_start();

	// Before
	do_action( 'wp_user_profiles_about_metabox_before', $user );

	// Filter action links
	add_filter( 'manage_sites_action_links', 'wp_user_profiles_filter_sites_action_links' );
	add_filter( 'wpmu_blogs_columns',        'wp_user_profiles_filter_sites_columns'      );

	// Output list table
	$wp_list_table->display();

	// Unfilter action links
	remove_filter( 'manage_sites_action_links', 'wp_user_profiles_filter_sites_action_links' );
	remove_filter( 'wpmu_blogs_columns',        'wp_user_profiles_filter_sites_columns'      );

	// After
	do_action( 'wp_user_profiles_about_metabox_after', $user );

	// Output the buffer
	echo ob_get_clean();
}

/**
 * Filter sites list-table query args, to only show sites the user is a member
 * of, for all networks in the installation.
 *
 * @since 1.0.0
 *
 * @param array $args
 *
 * @return array
 */
function wp_user_profiles_filter_sites_table_query_args( $args = array() ) {

	// Set site__in to site ID's of the user
	if ( isset( $GLOBALS['wp_user_profiles_site_in'] ) ) {
		$args['site__in'] = ! empty( $GLOBALS['wp_user_profiles_site_in'] )
			? $GLOBALS['wp_user_profiles_site_in']
			: array( '-1' ); // To return no sites

		// Unset network_id to show all sites in all networks
		unset( $args['network_id'] );
	}

	// Filter & return
	return apply_filters( 'wp_user_profiles_filter_sites_table_query_args', $args );
}

/**
 * Unset some links if user cannot manage sites
 *
 * @since 1.0.0
 *
 * @param array $links
 * @return array
 */
function wp_user_profiles_filter_sites_action_links( $links = array() ) {

	// Unset actionable links if user cannot manage sites
	if ( ! current_user_can( 'manage_sites' ) ) {
		unset(

			// Core
			$links['edit'],
			$links['activate'],
			$links['deactivate'],
			$links['archive'],
			$links['unarchive'],
			$links['spam'],
			$links['unspam'],
			$links['delete'],

			// WP Multi Network
			$links['move']
		);
	}

	return $links;
}

/**
 * Unset the checkbox column in user profiles
 *
 * @since 1.0.0
 *
 * @param array $columns
 * @return array
 */
function wp_user_profiles_filter_sites_columns( $columns = array() ) {
	unset( $columns['cb'] );
	return $columns;
}
