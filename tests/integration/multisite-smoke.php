<?php

defined( 'ABSPATH' ) || exit( 1 );

function wpup_multisite_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

wpup_multisite_assert( is_multisite(), 'Multisite is not enabled.' );
wpup_multisite_assert( function_exists( 'wp_user_profiles_get_common_user_roles_local_strategy' ), 'Plugin bootstrap did not load.' );

if ( ! function_exists( 'wpmu_delete_blog' ) || ! function_exists( 'wpmu_delete_user' ) ) {
	require_once ABSPATH . 'wp-admin/includes/ms.php';
}

$original_site_id = get_current_blog_id();
$original_user_id = get_current_user_id();
$run_id           = strtolower( wp_generate_password( 8, false, false ) );
$site_id          = 0;
$user_id          = 0;

$site_owner_id = $original_user_id;
if ( ! $site_owner_id ) {
	$administrator_ids = get_users(
		array(
			'role'   => 'administrator',
			'number' => 1,
			'fields' => 'ids',
		)
	);
	$site_owner_id = empty( $administrator_ids ) ? 0 : (int) reset( $administrator_ids );
}
wpup_multisite_assert( $site_owner_id, 'Could not find a site owner.' );

register_shutdown_function(
	function () use ( &$site_id, &$user_id, $original_site_id, $original_user_id ) {
		while ( get_current_blog_id() !== $original_site_id && ms_is_switched() ) {
			restore_current_blog();
		}

		wp_set_current_user( $original_user_id );

		if ( $user_id ) {
			wpmu_delete_user( $user_id );
		}

		if ( $site_id ) {
			wpmu_delete_blog( $site_id, true );
		}
	}
);

wp_set_current_user( $site_owner_id );

$site_result = wpmu_create_blog( "roles-{$run_id}.example.test", '/', 'Roles site', $site_owner_id );
wpup_multisite_assert( ! is_wp_error( $site_result ), 'Could not create a second site.' );
$site_id = (int) $site_result;

$user_result = wp_create_user( "wpup-network-user-{$run_id}", wp_generate_password(), "wpup-network-user-{$run_id}@example.test" );
wpup_multisite_assert( ! is_wp_error( $user_result ), 'Could not create network user.' );
$user_id = (int) $user_result;

// A user may save their own Sites section, but may not grant themselves a role.
wp_set_current_user( $user_id );
$_POST['action']   = 'add_as_administrator';
$_POST['allblogs'] = array( $site_id );
$sites_section     = $GLOBALS['wp_user_profile_sections']['sites'];
wpup_multisite_assert( $sites_section instanceof WP_User_Profile_Sites_Section, 'Sites section was not registered.' );
$sites_section->save( get_user_by( 'id', $user_id ) );
wpup_multisite_assert( ! is_user_member_of_blog( $user_id, $site_id ), 'User gained a site role without network permission.' );
unset( $_POST['action'], $_POST['allblogs'] );
wp_set_current_user( $site_owner_id );

$_POST['action']   = 'add_as_editor';
$_POST['allblogs'] = array( $site_id );
$sites_section->save( get_user_by( 'id', $user_id ) );
unset( $_POST['action'], $_POST['allblogs'] );
wpup_multisite_assert( is_user_member_of_blog( $user_id, $site_id ), 'Network administrator could not add user to second site.' );

$roles = wp_user_profiles_get_common_user_roles_local_strategy( $site_id );
wpup_multisite_assert( isset( $roles['editor'] ), 'Second-site editor role was not discovered.' );
wpup_multisite_assert( $original_site_id === get_current_blog_id(), 'Role discovery did not restore the original site.' );

switch_to_blog( $site_id );
$user = get_user_by( 'id', $user_id );
wpup_multisite_assert( in_array( 'editor', $user->roles, true ), 'User does not have the assigned second-site role.' );
restore_current_blog();
wpup_multisite_assert( $original_site_id === get_current_blog_id(), 'Manual role check did not restore the original site.' );

remove_user_from_blog( $user_id, $site_id );
wpup_multisite_assert( ! is_user_member_of_blog( $user_id, $site_id ), 'User was not removed from the second site.' );

echo "WP User Profiles multisite smoke test passed.\n";
