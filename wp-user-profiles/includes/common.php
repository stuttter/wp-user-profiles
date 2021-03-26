<?php

/**
 * User Profile Functions
 *
 * @package Plugins/Users/Profiles/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Are we looking at the currently logged in user's profile page?
 *
 * @since 2.1.0
 *
 * @return boolean
 */
function wp_is_profile_page() {
	$retval = defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE;

	return (bool) apply_filters( 'wp_is_profile_page', $retval );
}

/**
 * Set the `IS_PROFILE_PAGE` constant early.
 *
 * This function exists because the `IS_PROFILE_PAGE` constant is used in core
 * and by thousands of plugins to signify that a user is being edited. If it's
 * not set as early as possible, third party plugins are unable to predict what
 * to do early enough to hook in properly.
 *
 * @since 0.2.0
 */
function wp_user_profiles_set_constants() {

	// Get the current user ID
	$current_user_id = get_current_user_id();

	// Get the user ID being edited
	$user_id = ! empty( $_REQUEST['user_id'] )
		? absint( $_REQUEST['user_id'] )
		: $current_user_id;

	// Maybe set constant if editing oneself
	if ( ! wp_is_profile_page() ) {
		is_user_logged_in()
			? define( 'IS_PROFILE_PAGE', ( $user_id === $current_user_id ) )
			: define( 'IS_PROFILE_PAGE', false );
	}
}

/**
 * Return the file that all top-level admin area menus will use as their parent
 *
 * This function exists because WordPress bounces between different files for
 * different reasons in different admin area sections, and we need a way to
 * predict what that will be ahead of time.
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
		$file = 'admin.php';
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
	// @todo?
	} else {
		$url = wp_user_profiles_get_admin_area_url( $user_id, $scheme );
	}

	return add_query_arg( array(
		'page' => 'profile'
	), $url );
}

/**
 * Return an array of profile sections
 *
 * This function attempts to do some just-in-time backwards compatibility checks,
 * and prepares the return value for use through-out the plugin.
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
 * This function exists to enable rapid sorting of sections by the `order` value
 * of each section, allowing them to be located in any position regardless of
 * the order they were registered in.
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
 * Filter sections
 *
 * @since 2.0.0
 *
 * @param array $args
 * @param string $operator
 * @return object|array
 */
function wp_user_profiles_filter_sections( $args = array(), $operator = 'AND' ) {

	// Get all sections
	$all    = wp_user_profiles_sections();

	// Filter sections
	$retval = wp_list_filter( $all, $args, $operator );

	// Return the object if only 1 item was found and filtering by ID
	if ( ( 1 === count( $retval ) ) && array_key_exists( 'id', $args ) ) {
		$retval = reset( $retval );
	}

	// Filter & return
	return apply_filters( 'wp_user_profiles_filter_sections', $retval, $args, $operator, $all );
}

/**
 * Get profile section slugs
 *
 * This function exists because hooknames change based on two unique factors:
 * which admin dashboard is being used, and which file is used within that
 * dashboard. Without this function, network & user dashboard integration
 * would be impossible, so be sure to use this when trying to narrow your scope.
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
	array_walk( $hooks, 'wp_user_profiles_walk_section_hooknames' );

	return $hooks;
}

/**
 * Walk array and add maybe add network or user suffix
 *
 * This function exists to be used byref by array_walk() to manipulate screen
 * hooks so that user & network dashboard integration is possible without a
 * bunch of additional work.
 *
 * @since 0.1.7
 *
 * @param string $value
 */
function wp_user_profiles_walk_section_hooknames( &$value = '' ) {
	if ( is_network_admin() && substr( $value, -8 ) !== '-network' ) {
		$value .= '-network';
	} elseif ( is_user_admin() && substr( $value, -5 ) != '-user' ) {
		$value .= '-user';
	}
}

/**
 * Return the admin area URL for a user
 *
 * This function exists to make it easier to determine which admin area URL to
 * use in what context. It also comes with its own filter to make it easier to
 * target its usages.
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
		// Using the current user id and changing it later, as using a user that does not belong to the site will
		// trigger a 404 redirect to on the main site of the network
		$url = get_dashboard_url( get_current_user_id(), $file, $scheme );
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

/**
 * Get the data of the user being edit
 *
 * @since 2.0.0
 *
 * @param int $user_id ID of user to get for editing
 *
 * @return WP_User
 */
function wp_user_profiles_get_user_to_edit( $user_id = 0 ) {

	// Get the user ID being edited
	if ( empty( $user_id ) ) {
		$user_id = ! empty( $_GET['user_id'] )
			? $_GET['user_id']
			: get_current_user_id();
	}

	// Cast to INT because we can't be sure where this came from
	$user_id = (int) $user_id;

	// Get the user to edit
	$user = get_userdata( $user_id );

	// Set user filter to 'edit'
	if ( ! empty( $user ) ) {
		$user->filter = 'edit';
	} else {
		$user = new WP_User();
	}

	// Return the user to edit
	return $user;
}

/**
 * Save the user when they click "Update"
 *
 * This function exists to handle the posted user information, likely submitted
 * by a user or administrator to edit an existing user account.
 *
 * @since 0.1.0
 * @global $pagenow Current admin page.
 */
function wp_user_profiles_save_user() {
	global $pagenow;

	// Bail if not updating a user
	if ( empty( $_POST['user_id'] ) || empty( $_POST['action'] ) ) {
		return;
	}

	// Bail if user is not logged in
	if ( ! is_user_logged_in() ) {
		return;
	}

	// Bail if not processing the user editing page
	if ( ! in_array( $pagenow, array( 'users.php', 'admin.php' ), true ) ) {
		return;
	}

	// Bail if not a registered section
	$sections = wp_list_pluck( wp_user_profiles_sections(), 'id' );
	if ( ! ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $sections ) ) ) {
		return;
	}

	// Set the user ID
	$user_id = (int) $_POST['user_id'];

	// Nonce check
	check_admin_referer( 'update-user_' . $user_id );

	// Bail if user cannot edit this user
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	// Setup constant for backpat
	if ( ! defined( 'IS_PROFILE_PAGE' ) ) {
		define( 'IS_PROFILE_PAGE', get_current_user_id() === $user_id );
	}

	// Remove the multisite email change action for now to prevent notices
	remove_action( 'personal_options_update', 'send_confirmation_on_profile_email' );

	// This filter documented in wp-admin/user-edit.php
	wp_is_profile_page()
		? do_action( 'personal_options_update',  $user_id )
		: do_action( 'edit_user_profile_update', $user_id );

	// Get the userdata to compare it to
	$user = wp_user_profiles_get_user_to_edit( $user_id, false );

	// Do actions & return errors
	$status = apply_filters( 'wp_user_profiles_save', $user );

	// No errors
	if ( ! is_wp_error( $status ) ) {

		// Add updated query arg to trigger success notice
		$redirect = add_query_arg( array(
			'action'  => 'update',
			'updated' => 'true',
			'page'    => isset( $_GET['page'] )
				? sanitize_key( $_GET['page'] )
				: 'profile'
		), get_edit_user_link( $user_id ) );

		// Referring?
		$wp_http_referer = ! empty( $_REQUEST['wp_http_referer'] )
			? $_REQUEST['wp_http_referer']
			: false;

		// Add referer query arg to redirect to next
		if ( ! empty( $wp_http_referer ) ) {
			$redirect = add_query_arg( array(
				'wp_http_referer' => urlencode( $wp_http_referer )
			), $redirect );
		}

		// Redirect
		wp_safe_redirect( $redirect );
		exit;

	// Errors
	} else {
		wp_die( $status );
	}
}

/**
 * Add a notice when a profile is updated
 *
 * This function exists to provide visual confirmation that a users details were
 * successfully saved.
 *
 * @since 0.1.0
 *
 * @param  mixed $notice
 * @return array
 */
function wp_user_profiles_save_user_notices() {

	// Bail if not an update action
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

/**
 * Do the core show/edit actions have output associated with them?
 *
 * By default, this is hooked to `wp_user_profiles_show_other_section` but can
 * be used anywhere as needed (in your custom sections, etc...)
 *
 * @since 2.2.0
 *
 * @return bool
 */
function wp_user_profiles_has_profile_actions() {

	// Which hook to check for actions
	$action = wp_is_profile_page()
		? 'show_user_profile'
		: 'edit_user_profile';

	// Check if hooks have been added
	$retval = (bool) has_action( $action );

	// Return true/false
	return $retval;
}

/**
 * Maybe unhook the BuddyPress Profile Navigation hook
 *
 * BuddyPress hooks into 'show_user_profile' and 'edit_user_profile' for
 * displaying its custom UI, it conflicts with ours, so the best thing we can do
 * is avoid the situation completely.
 *
 * @since 2.4.0
 */
function wp_user_profiles_unhook_bp_profile_nav() {

	// Bail if no BuddyPress
	if ( ! function_exists( 'buddypress' ) ) {
		return;
	}

	// Get BuddyPress Profile Navigation hook
	$bp = buddypress();

	// Bail if no Member Admin
	if ( empty( $bp->members->admin ) ) {
		return;
	}

	// Get the hook callback
	$tag = array( $bp->members->admin, 'profile_nav' );

	// Remove the actions
	remove_action( 'show_user_profile', $tag, 99 );
	remove_action( 'edit_user_profile', $tag, 99 );
}

/**
 * Do an action inside of an output buffer.
 *
 * @since 2.6.0
 *
 * @param string $action
 * @param WP_User $user
 *
 * @return mixed
 */
function wp_user_profiles_buffer_action( $action = '', $user = null ) {

	// Start an output buffer
	ob_start();

	// Do the action
	do_action( $action, $user );

	// Return the current buffer
	return ob_get_clean();
}
