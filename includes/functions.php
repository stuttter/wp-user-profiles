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
 * Update the status of a user in the database.
 *
 * @since 0.1.3
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int    $user       The user.
 * @param string $pref       The column in the wp_users table to update the user's status
 *                           in (presumably user_status, spam, or deleted).
 * @param int    $value      The new status for the user.
 *
 * @return int   The initially passed $value.
 */
function wp_user_profiles_update_user_status( $user, $status = 'inactive' ) {
	global $wpdb;

	// Get the user
	$user = new WP_User( $user );

	// Save the old status for help with transitioning
	$old_status = $user->user_status;

	// Update user status accordingly
	if ( 'spam' === $status ) {
		$wpdb->update( $wpdb->users, array( 'user_status' => '1', 'spam' => '1' ), array( 'ID' => $user->ID ) );
	} elseif ( 'ham' === $status ) {
		$wpdb->update( $wpdb->users, array( 'user_status' => '0', 'spam' => '0' ), array( 'ID' => $user->ID ) );
	} elseif ( 'deleted' === $status ) {
		$wpdb->update( $wpdb->users, array( 'user_status' => '2', 'deleted' => '1' ), array( 'ID' => $user->ID ) );
	} elseif ( 'undeleted' === $status ) {
		$wpdb->update( $wpdb->users, array( 'user_status' => '0', 'deleted' => '0' ), array( 'ID' => $user->ID ) );
	} elseif ( 'inactive' === $status ) {
		$wpdb->update( $wpdb->users, array( 'user_status' => '2', 'spam' => '0', 'deleted' => '0' ), array( 'ID' => $user->ID ) );
	} else {
		$wpdb->update( $wpdb->users, array( 'user_status' => '0', 'spam' => '0', 'deleted' => '0' ), array( 'ID' => $user->ID ) );
	}

	// Bust the user's cache
	clean_user_cache( $user );

	// Get the user, again
	$user = new WP_User( $user );

	// Backpat for multisite
	if ( 'spam' === $status ) {
		do_action( 'make_spam_user', $user->ID );
	} elseif ( 'active' === $status ) {
		do_action( 'make_ham_user', $user->ID );
	}

	// Transition a user from one status to another
	wp_user_profiles_transition_user_status( $user->user_status, $old_status, $user );

	return $user;
}

/**
 * Fires actions related to the transitioning of a user's status.
 *
 * When a user is saved, the user status is "transitioned" from one status to another,
 * though this does not always mean the status has actually changed before and after
 * the save. This function fires a number of action hooks related to that transition:
 * the generic 'transition_user_status' action, as well as the dynamic hooks
 * `"{$old_status}_to_{$new_status}"` and `"{$new_status}_{$user->user_type}"`. Note
 * that the function does not transition the user object in the database.
 *
 * For instance: When activating a user for the first time, the user status may transition
 * from 'inactive' – or some other status – to 'active'. However, if a user is already
 * active and is simply being updated, the "old" and "new" statuses may both be 'active'
 * before and after the transition.
 *
 * @since 0.1.3
 *
 * @param string  $new_status Transition to this user status.
 * @param string  $old_status Previous user status.
 * @param WP_User $user       User data.
 */
function wp_user_profiles_transition_user_status( $new_status, $old_status, $user ) {

	/**
	 * Fires when a user is transitioned from one status to another.
	 *
	 * @since 0.1.3
	 *
	 * @param string  $new_status New user status.
	 * @param string  $old_status Old user status.
	 * @param WP_User $user       User object.
	 */
	do_action( 'transition_user_status', $new_status, $old_status, $user );

	/**
	 * Fires when a user is transitioned from one status to another.
	 *
	 * The dynamic portions of the hook name, `$new_status` and `$old status`,
	 * refer to the old and new user statuses, respectively.
	 *
	 * @since 0.1.3
	 *
	 * @param WP_User $user User object.
	 */
	do_action( "{$old_status}_to_{$new_status}", $user );

	/**
	 * Fires when a user is transitioned from one status to another.
	 *
	 * The dynamic portions of the hook name, `$new_status` and `$user->user_type`,
	 * refer to the new user status and user type, respectively.
	 *
	 * Please note: When this action is hooked using a particular user status (like
	 * 'publish', as `publish_{$user->user_type}`), it will fire both when a user is
	 * first transitioned to that status from something else, as well as upon
	 * subsequent user updates (old and new status are both the same).
	 *
	 * Therefore, if you are looking to only fire a callback when a user is first
	 * transitioned to a status, use the {@see 'transition_user_status'} hook instead.
	 *
	 * @since 0.1.3
	 *
	 * @param int     $user_id User ID.
	 * @param WP_User $user    User object.
	 */
	do_action( "{$new_status}_{$user->user_type}", $user->ID, $user );
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
