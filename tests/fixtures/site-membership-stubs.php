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

/**
 * Return the user's site memberships.
 *
 * @param int $user_id User ID.
 * @return array Site memberships.
 */
function get_blogs_of_user( $user_id ) {
	return $GLOBALS['wpup_test']['user_blogs'][ $user_id ] ?? array();
}

/**
 * Record user metadata changes.
 *
 * @param int    $user_id User ID.
 * @param string $key     Metadata key.
 * @param mixed  $value   Metadata value.
 * @return true
 */
function update_user_meta( $user_id, $key, $value ) {
	wpup_test_call( __FUNCTION__, array( $user_id, $key, $value ) );
	return true;
}
