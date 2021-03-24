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

	// Get the user
	$user = new WP_User( $user );

	// Bail if anonymous user
	if ( empty( $user->ID ) ) {
		return $user;
	}

	// Bail if super administrator
	if ( is_multisite() && is_super_admin( $user->ID ) ) {
		return $user;
	}

	// Save the old status for help with transitioning
	$old_status = $user->user_status;

	// What's the new status?
	switch ( $status ) {

		// Deleted
		case 'deleted' :
			$where = is_multisite()
				? array( 'user_status' => '2', 'deleted' => '1' )
				: array( 'user_status' => '2' );
			break;

		// Undeleted
		case 'undeleted' :
			$where = is_multisite()
				? array( 'user_status' => '0', 'deleted' => '0' )
				: array( 'user_status' => '0' );
			break;

		// Inactive
		case 'inactive' :
			$where = is_multisite()
				? array( 'user_status' => '2', 'spam' => '0', 'deleted' => '0' )
				: array( 'user_status' => '2' );
			break;

		// Spammer
		case 'spam' :
			$where = is_multisite()
				? array( 'user_status' => '1', 'spam' => '1' )
				: array( 'user_status' => '1' );
			break;

		// Not a Spammer/Active
		case 'ham' :
		default :
			$where = is_multisite()
				? array( 'user_status' => '0', 'spam' => '0', 'deleted' => '0' )
				: array( 'user_status' => '0' );
			break;
	}

	// Only update the database if changing
	if ( $status !== $old_status ) {
		global $wpdb;

		// Attempt the database query
		$result = $wpdb->update( $wpdb->users, $where, array( 'ID' => $user->ID ) );

		// Query succeeded
		if ( ! empty( $result ) ) {

			// Bust the user's cache
			clean_user_cache( $user );

			// Get the user, again
			$user = new WP_User( $user );
		}
	}

	// Transition a user from one status to another
	wp_user_profiles_transition_user_status( $user->user_status, $old_status, $user );

	// Return the (possibly updated) user
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

	// Backwards compatibility for multisite spamming
	if ( ( 'spam' === $new_status ) && ( 'spam' !== $old_status ) ) {
		do_action( 'make_spam_user', $user->ID );

	// Backwards compatibility for multisite unspamming
	} elseif ( ( 'active' === $new_status ) && ( 'active' !== $old_status ) ) {
		do_action( 'make_ham_user', $user->ID );
	}

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
 * Update the user status, from fields in the "Status" metabox.
 *
 * Filters the user object
 *
 * @since 2.5.1
 *
 * @param WP_User $user
 */
function wp_user_profiles_save_user_status( $user = null ) {

	// Maybe update user status
	if ( ! empty( $_POST['user_status'] ) ) {

		// Sanitize the posted status
		$status = sanitize_key( $_POST['user_status'] );

		// Returns a (possibly) updated WP_User object
		if ( ! empty( $status ) ) {
			$user = wp_user_profiles_update_user_status( $user, $status );
		}
	}

	// Return the (possibly filtered) user
	return $user;
}
