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
 * Return a configured site, or a valid site for existing tests.
 *
 * @param int $site_id Site ID.
 * @return object|false Site or false.
 */
function get_site( $site_id ) {
	if ( array_key_exists( $site_id, $GLOBALS['wpup_test']['sites'] ?? array() ) ) {
		return $GLOBALS['wpup_test']['sites'][ $site_id ];
	}

	return (object) array(
		'site_id' => 1,
		'domain'  => 'example.test',
	);
}

/**
 * Check whether the current user can manage a network.
 *
 * @param int $network_id Network ID.
 * @return bool Whether network edits are allowed.
 */
function can_edit_network( $network_id ) {
	wpup_test_call( __FUNCTION__, array( $network_id ) );
	return $GLOBALS['wpup_test']['can_edit_network'] ?? true;
}

/**
 * Check whether a user belongs to a site.
 *
 * @param int $user_id User ID.
 * @param int $site_id Site ID.
 * @return bool Whether the user belongs to the site.
 */
function is_user_member_of_blog( $user_id, $site_id ) {
	return $GLOBALS['wpup_test']['memberships'][ $user_id ][ $site_id ] ?? false;
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
