<?php
/**
 * Record site membership changes in PHPUnit tests.
 *
 * @package WP_User_Profiles
 */

/**
 * Record site membership additions.
 *
 * @param int    $blog_id Site ID.
 * @param int    $user_id User ID.
 * @param string $role    Role name.
 * @return true
 */
function add_user_to_blog( $blog_id, $user_id, $role ) {
	wpup_test_call( __FUNCTION__, array( $blog_id, $user_id, $role ) );
	return true;
}

/**
 * Record site membership removals.
 *
 * @param int $user_id User ID.
 * @param int $blog_id Site ID.
 * @return true
 */
function remove_user_from_blog( $user_id, $blog_id ) {
	wpup_test_call( __FUNCTION__, array( $user_id, $blog_id ) );
	return true;
}
