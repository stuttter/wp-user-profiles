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

	$is_network_admin = current_user_can( 'manage_sites' );
	$all_sites = $is_network_admin && ! empty( $_GET['all_sites'] );

	// Get sites for user
	$sites  = get_blogs_of_user( $user->ID, true );
	$screen = get_current_screen();

	// Force screen to setup list table
	set_current_screen( 'network-sites' );
	$wp_list_table = _get_list_table( 'WP_MS_Sites_List_Table' );

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
	do_action( 'wp_user_profiles_sites_metabox_before', $user );

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
	do_action( 'wp_user_profiles_sites_metabox_after', $user );

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
function wp_user_profiles_filter_sites_action_links( $links = array(), $blog_id ) {

	if ( current_user_can( 'manage_sites' ) ) {
		$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : get_current_user_id();

		if ( is_user_member_of_blog( $user_id, $blog_id ) ) {
			$links['remove'] = sprintf(
				'<br/><a href="%s">%s</a>',
				add_query_arg( array( 'action' => 'remove', 'site' => $blog_id, 'user' => $user_id ) ),
				esc_html__( 'Remove as a member', 'wp-user-profiles' )
			);
		}
	} else {
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

	$views['assigned'] = "<a href='" . esc_url( add_query_arg( 'all_sites', 0 ) ) . "'" . ( ! $all_sites ? 'class="current"' : '' ) . '>' . esc_html__( 'Assigned', 'wp-user-profiles' ) . '</a>';
	$views['all'] = "<a href='" . esc_url( add_query_arg( 'all_sites', 1 ) ) . "'" . ( $all_sites ? 'class="current"' : '' ) . '>' . esc_html__( 'All Sites', 'wp-user-profiles' ) . '</a>';

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
	$actions = [
		'remove' => esc_html__( 'Remove', 'wp-user-profiles' ),
		'add' => esc_html__( 'Add as..', 'wp-user-profiles' ),
	];

	foreach ( wp_user_profiles_get_common_user_roles() as $role => $name ) {
		// translators: prefix for roles dropdown
		$actions[ 'add_as_' . $role ] = sprintf( esc_html_x( '- %s', 'translators: prefix for roles dropdown', 'wp-user-profile' ), $name );
	};

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
function wp_user_profiles_filter_role_column( $column_name, $blog_id ) {
	if ( $column_name !== 'roles' ) {
		return;
	}

	global $wp_site_roles_cache;
	$wp_site_roles_cache = $wp_site_roles_cache ? $wp_site_roles_cache : array();

	$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : get_current_user_id();

	switch_to_blog( $blog_id );
	$user = get_user_by( 'id', $user_id );
	$wp_site_roles_cache[ $blog_id ] = get_editable_roles();
	$roles = wp_list_pluck( array_intersect_key( $wp_site_roles_cache[ $blog_id ], array_flip( $user->roles ) ), 'name' );
	restore_current_blog();

	if ( ! empty( $roles ) ) {
		echo implode( ',', array_map( 'esc_html', $roles ) );
	} else {
		echo '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . esc_html__( 'User is not a member of this site' ) . '</span>';
	}
}

/**
 * Return a list of common user roles from a list of sites
 *
 * @param array|null $site_ids  List of site IDs to grab roles from
 *
 * @return array|bool|mixed
 */
function wp_user_profiles_get_common_user_roles( array $site_ids = null ) {

	// nonce check
	// catch list of site ids

	// Allow filtering this to disable querying all sites for roles
	$roles = apply_filters( 'pre_wp_user_profiles_common_user_roles', false );

	if ( $roles ) {
		return $roles;
	}

	$cache_ttl = apply_filters( 'wp_common_user_roles_cache_ttl', DAY_IN_SECONDS );
	$cache_key = 'wp_user_profiles_common_user_roles';

	$cached = get_site_transient( $cache_key );
	if ( $cached && is_array( $cached ) ) {
		return $cached;
	}

	// Store a nonce network-wide
	$nonce = wp_create_nonce( 'wp-user-profiles' );
	update_site_option( 'wp-user-profiles-nonce-' . get_current_user_id(), $nonce );

	/**
	 * Filter wp_user_roles_main_sites
	 *
	 * Filters what sites are queried for roles
	 *
	 * @param int Array of blog ids
	 */
	$roles_site_ids = apply_filters( 'wp_user_roles_main_sites', $site_ids );
	if ( $roles_site_ids ) {
		$sites = new WP_Site_Query([
			'fields'   => 'ids', // Just to validate sites exists
			'site__in' => (array) $roles_site_ids,
		]);

	} else {
		/**
		 * Filters the number of sites queried for roles
		 *
		 * @param int Number of blogs
		 */
		$site_query_limit = apply_filters( 'wp_user_roles_main_sites', 10 );
		$sites = new WP_Site_Query([
			'fields'   => 'ids',
			'limit'    => $site_query_limit,
			'orderby'  => 'id',
		]);
	}

	// Batch request roles from each site
	if ( $sites ) {
		$roles = array_reduce( $sites->sites, function ( $roles, $blog_id ) use ( $nonce ) {
			$url = add_query_arg( [
				'action' => 'wp_user_profiles_export_roles',
				'nonce'  => $nonce,
				'auth'   => get_current_user_id(),
			], get_admin_url( $blog_id ) . '/admin-ajax.php' );

			$response = wp_remote_get( $url, ['timeout' => 20] );

			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$data = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset( $data['data'] ) ) {
					$roles[ $blog_id ] = $data['data'];
				}
			}

			return $roles;
		}, [] );

		// Get intersected roles between all sites
		$common_roles = call_user_func_array( 'array_intersect_key', $roles );
	}

	set_site_transient( $cache_key, $common_roles, $cache_ttl );

	delete_site_option( 'wp-user-profiles-nonce-' . get_current_user_id() );

	return $common_roles;
}

/**
 * AJAX endpoint to query roles from specific list of sites
 *
 * @action wp_ajax_wp_user_profiles_common_roles
 */
function wp_user_profiles_get_common_user_roles_ajax() {
	check_admin_referer( 'wp_user_profiles_common_roles', 'nonce' );

	$site_ids = isset( $_GET['sites'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['sites'] ) ) : []; // wpcs: input var okay

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
	$nonce   = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : ''; // wpcs: csrf input var okay
	$user_id = isset( $_GET['auth'] ) ? sanitize_text_field( wp_unslash( $_GET['auth'] ) ) : ''; // wpcs: csrf input var okay

	if ( empty( $nonce ) || $nonce !== get_site_option( 'wp-user-profiles-nonce-' . $user_id ) ) {
		wp_send_json_error( esc_html__( 'Invalid nonce received', 'wp-user-profiles' ), 401 );
	}

	/** @var $wp_roles \WP_Roles */
	global $wp_roles;
	$roles = wp_list_pluck( $wp_roles->roles, 'name' );
	wp_send_json_success( $roles );
}
