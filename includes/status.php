<?php

/**
 * User Profile Statuses
 *
 * @package Plugins/Users/Profiles/Status
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
 * Grant or revoke super admin status
 *
 * This function exists to assist with updating whether a user is an
 * administrator to the entire installation.
 *
 * @since 0.2.0
 *
 * @param int $user
 */
function wp_user_profiles_update_global_admin( $user = null ) {

	// Grant or revoke super admin status if requested.
	if ( is_a( $user, 'WP_User' ) && is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) && ! isset( $GLOBALS['super_admins'] ) && empty( $_POST['super_admin'] ) == is_super_admin( $user->ID ) ) {
		empty( $_POST['super_admin'] )
			? revoke_super_admin( $user->ID )
			: grant_super_admin( $user->ID );
	}

	// Return the user
	return $user;
}
