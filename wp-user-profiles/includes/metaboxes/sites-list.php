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
	require_once dirname( __DIR__ ) . '/sites-list-table.php';

	$is_network_admin = current_user_can( 'manage_sites' );
	$all_sites = $is_network_admin && ! empty( $_GET['all_sites'] );

	// Get sites for user
	$sites  = get_blogs_of_user( $user->ID, true );
	$screen = get_current_screen();

	// Force screen to setup list table
	set_current_screen( 'network-sites' );
	$wp_list_table = new WP_User_Profiles_Sites_List_Table( array(
		'screen' => get_current_screen(),
	) );

	// Override sites query
	if ( $all_sites || ! empty( $sites ) ) {
		$GLOBALS['wp_user_profiles_site_in'] = $all_sites ? array() : array_keys( $sites );
		add_filter( 'ms_sites_list_table_query_args', 'wp_user_profiles_filter_sites_table_query_args' );

		// Get the list table & items
		$wp_list_table->prepare_items();

		// Reset sites query
		remove_filter( 'ms_sites_list_table_query_args', 'wp_user_profiles_filter_sites_table_query_args' );
		unset( $GLOBALS['wp_user_profiles_site_in'] );
	}

	// Reset screen
	set_current_screen( $screen->id );

	// No bulk actions for non-network-admins
	if ( ! $is_network_admin ) {
		$wp_list_table->_actions = false;
	}

	// Start
	ob_start();

	// Before
	do_action( __FUNCTION__ . '_before', $user );

	// Filter action links
	add_filter( 'manage_sites_action_links',   'wp_user_profiles_filter_sites_action_links', 10, 2 );
	add_filter( 'wpmu_blogs_columns',          'wp_user_profiles_filter_sites_columns'      );
	add_filter( 'views_network-sites',         'wp_user_profiles_filter_views'              );
	add_filter( 'bulk_actions-network-sites',  'wp_user_profiles_filter_bulk_actions'       );
	add_filter( 'manage_sites_custom_column',  'wp_user_profiles_filter_role_column', 10, 2 );

	$wp_list_table->views();

	// Output list table
	$wp_list_table->display();

	// Unfilter action links
	remove_filter( 'manage_sites_action_links', 'wp_user_profiles_filter_sites_action_links' );
	remove_filter( 'wpmu_blogs_columns',        'wp_user_profiles_filter_sites_columns'      );

	// After
	do_action( __FUNCTION__ . '_after', $user );

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
	if ( ! empty( $GLOBALS['wp_user_profiles_site_in'] ) ) {
		$args['site__in'] = $GLOBALS['wp_user_profiles_site_in'];

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
function wp_user_profiles_filter_sites_action_links( $links = array(), $blog_id = 0 ) {

	if ( ! current_user_can( 'manage_sites' ) ) {
		// Unset actionable links
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
	$is_network_admin = current_user_can( 'manage_sites' );

	if ( $is_network_admin ) {
		$columns['roles'] = esc_html__( 'Roles', 'wp-user-profiles' );
	} else {
		unset( $columns['cb'] );
	}

	unset( $columns['lastupdated'], $columns['registered'] );

	return $columns;
}

/**
 * Show different views/filters for list of sites
 *
 * @param array $views
 *
 * @return array
 */
function wp_user_profiles_filter_views( $views = array() ) {
	$all_sites = ! empty( $_GET['all_sites'] );

	// Default views
	$views = array(
		'assigned' => "<a href='" . esc_url( add_query_arg( 'all_sites', 0 ) ) . "#sites'" . ( ! $all_sites ? 'class="current"' : '' ) . '>' . esc_html__( 'Assigned',  'wp-user-profiles' ) . '</a>',
		'all'      => "<a href='" . esc_url( add_query_arg( 'all_sites', 1 ) ) . "#sites'" . (   $all_sites ? 'class="current"' : '' ) . '>' . esc_html__( 'All Sites', 'wp-user-profiles' ) . '</a>'
	);

	return $views;
}

/**
 * Filter list of actions
 *
 * @param array $actions
 *
 * @return array
 */
function wp_user_profiles_filter_bulk_actions( $actions = array() ) {

	// Default actions
	$actions = array(
		'remove' => esc_html__( 'Remove', 'wp-user-profiles' ),
	);

	// Looking at "All Sites" or not
	$all_sites = isset( $_GET['all_sites'] )
		? absint( $_GET['all_sites'] )
		: 0;

	// Maybe add more if looking at "All Sites"
	if ( ! empty( $all_sites ) ) {

		// Add the "add" action
		$actions['add'] = esc_html__( 'Assign as (Choose a Role)', 'wp-user-profiles' );

		// Add combined roles
		foreach ( wp_user_profiles_get_common_user_roles() as $role => $name ) {

			// translators: prefix for roles dropdown
			$actions[ 'add_as_' . $role ] = sprintf( _x( '&mdash; %s', 'translators: prefix for roles dropdown', 'wp-user-profiles' ), $name );
		}
	}

	return $actions;
}

/**
 * Output user roles for a blog
 *
 * @param string $column_name
 * @param int    $blog_id
 *
 * @return string
 */
function wp_user_profiles_filter_role_column( $column_name = '', $blog_id = 0 ) {
	static $wp_site_roles_cache = array();

	// Bail if not the "Roles" column
	if ( 'roles' !== $column_name ) {
		return;
	}

	$user_id = isset( $_GET['user_id'] )
		? absint( $_GET['user_id'] )
		: get_current_user_id();

	switch_to_blog( $blog_id );
	$user = get_user_by( 'id', $user_id );
	$wp_site_roles_cache[ $blog_id ] = get_editable_roles();
	$roles = wp_list_pluck( array_intersect_key( $wp_site_roles_cache[ $blog_id ], array_flip( $user->roles ) ), 'name' );
	restore_current_blog();

	if ( ! empty( $roles ) ) {
		echo implode( ',', array_map( 'esc_html', $roles ) );
	} else {
		echo '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . esc_html__( 'User is not a member of this site', 'wp-user-profiles' ) . '</span>';
	}
}

/**
 * Return a list of common user roles from a list of sites
 *
 * @param array|null $site_ids List of site IDs to grab roles from
 *
 * @return array|bool|mixed
 */
function wp_user_profiles_get_common_user_roles( array $site_ids = null ) {

	// Allow filtering this to disable querying all sites for roles
	$roles = apply_filters( 'pre_wp_user_profiles_common_user_roles', false );

	// Use pre-fabbed roles
	if ( false !== $roles ) {
		return $roles;
	}

	// Transient cache info
	$cache_ttl = apply_filters( 'wp_common_user_roles_cache_ttl', DAY_IN_SECONDS );
	$cache_key = 'wp_user_profiles_common_user_roles';

	// Use transient cache
	$cached = get_site_transient( $cache_key );
	if ( ! empty( $cached ) && is_array( $cached ) ) {
		return $cached;
	}

	// Use the current user ID for caching
	$user_id = get_current_user_id();

	// Use the current network (for now)
	$network_id = get_current_network_id();

	// Store a nonce network-wide
	$nonce     = wp_create_nonce( 'wp-user-profiles' );
	$nonce_key = 'wp-user-profiles-nonce-' . $user_id;
	update_network_option( $network_id, $nonce_key, $nonce );

	/**
	 * Filters what sites are queried for roles
	 *
	 * @param int Array of blog ids
	 */
	$roles_site_ids = apply_filters( 'wp_user_roles_main_sites', $site_ids );

	/**
	 * Filters the number of sites queried for roles
	 *
	 * @param int Number of blogs
	 */
	$site_query_limit = apply_filters( 'wp_user_roles_main_sites', 10 );

	// Query by site IDs
	if ( ! empty( $roles_site_ids ) ) {
		$args = array(
			'fields'   => 'ids',
			'site__in' => (array) $roles_site_ids,
			'orderby'  => 'id',
		);

	// Query by site IDs, limited to X number
	} else {
		$args = array(
			'fields'   => 'ids',
			'limit'    => absint( $site_query_limit ),
			'orderby'  => 'id',
		);
	}

	// Common roles
	$common_roles = array(
		'__default__' => esc_html__( 'Default site role', 'wp-user-profiles' ),
	);

	// Initial reduction array
	$reduced = array();

	// Query for site IDs
	$sites = new WP_Site_Query( $args );

	// Batch request roles from each site
	if ( ! empty( $sites->sites ) ) {

		// Initial roles array
		$roles = array();

		// Loop through sites
		foreach ( $sites->sites as $site_id ) {

			// Admin URL
			$url = get_admin_url( $site_id ) . '/admin-ajax.php';

			// Remote URL
			$remote = add_query_arg( array(
				'action' => 'wp_user_profiles_export_roles',
				'nonce'  => $nonce,
				'auth'   => $user_id,
			), $url );

			// Remote request
			$response = wp_remote_get( $remote, array(
				'timeout' => 10
			) );

			// Get response code
			$code = wp_remote_retrieve_response_code( $response );

			// Success
			if ( 200 === $code ) {

				// Get info
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );

				// Look for data
				if ( ! empty( $data['data'] ) ) {
					$roles[ $site_id ] = $data['data'];
				}
			}
		}

		// Get all possible roles and reduce them
		$all     = call_user_func_array( 'array_merge', $roles );
		$reduced = array_reduce( $roles, 'array_intersect_key', $all );
	}

	// Setup the return value
	$retval = ! empty( $reduced )
		? $common_roles + $reduced
		: $common_roles;

	// Always stash the roles
	set_site_transient( $cache_key, $retval, $cache_ttl );

	// Always delete the nonce
	delete_network_option( $network_id, $nonce_key );

	// Return the roles
	return $retval;
}

/**
 * AJAX endpoint to query roles from specific list of sites
 *
 * @action wp_ajax_wp_user_profiles_common_roles
 */
function wp_user_profiles_get_common_user_roles_ajax() {
	check_admin_referer( 'wp_user_profiles_common_roles', 'nonce' );

	$site_ids = isset( $_GET['sites'] ) // wpcs: input var okay
		? array_map( 'absint', (array) wp_unslash( $_GET['sites'] ) ) // wpcs: input var okay
		: array();

	$roles = wp_user_profiles_get_common_user_roles( $site_ids );

	wp_send_json_success( $roles );
}

/**
 * AJAX endpoint to query roles from the current site
 *
 * @action wp_ajax_wp_user_profiles_export_roles
 * @action wp_ajax_nopriv_wp_user_profiles_export_roles
 */
function wp_user_profiles_export_user_roles_ajax() {

	// Get the nonce
	$nonce = isset( $_GET['nonce'] ) // wpcs: csrf input var okay
		? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) // wpcs: csrf input var okay
		: '';

	// Get the user ID
	$user_id = isset( $_GET['auth'] ) // wpcs: csrf input var okay
		? absint( wp_unslash( $_GET['auth'] ) ) // wpcs: csrf input var okay
		: '';

	// Success
	if ( ! empty( $user_id ) && ! empty( $nonce ) && ( $nonce === get_network_option( null, 'wp-user-profiles-nonce-' . $user_id ) ) ) {

		/** @var $wp_roles \WP_Roles */
		global $wp_roles;

		$roles = wp_list_pluck( $wp_roles->roles, 'name' );

		wp_send_json_success( $roles );
	}

	// Default error
	wp_send_json_error( esc_html__( 'Invalid nonce or user ID.', 'wp-user-profiles' ), 401 );
}
