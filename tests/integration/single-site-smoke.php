<?php

defined( 'ABSPATH' ) || exit( 1 );

function wpup_smoke_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

wpup_smoke_assert( function_exists( 'wp_user_profiles_save_user' ), 'Plugin bootstrap did not load.' );
wpup_smoke_assert( has_action( 'admin_init', 'wp_user_profiles_save_user' ), 'Save handler is not registered.' );
wpup_smoke_assert( has_filter( 'map_meta_cap', 'wp_user_profiles_map_meta_cap' ), 'Capability mapper is not registered.' );

if ( ! function_exists( 'wp_delete_user' ) ) {
	require_once ABSPATH . 'wp-admin/includes/user.php';
}

$run_id           = strtolower( wp_generate_password( 8, false, false ) );
$administrator_id = 0;
$subscriber_id    = 0;
$original_user_id = get_current_user_id();

register_shutdown_function(
	function () use ( &$administrator_id, &$subscriber_id, $original_user_id ) {
		wp_set_current_user( $original_user_id );

		if ( $subscriber_id ) {
			wp_delete_user( $subscriber_id );
		}

		if ( $administrator_id ) {
			wp_delete_user( $administrator_id );
		}
	}
);

$administrator_result = wp_create_user( "wpup-admin-{$run_id}", wp_generate_password(), "wpup-admin-{$run_id}@example.test" );
wpup_smoke_assert( ! is_wp_error( $administrator_result ), 'Could not create administrator.' );
$administrator_id = (int) $administrator_result;

$subscriber_result = wp_create_user( "wpup-subscriber-{$run_id}", wp_generate_password(), "wpup-subscriber-{$run_id}@example.test" );
wpup_smoke_assert( ! is_wp_error( $subscriber_result ), 'Could not create subscriber.' );
$subscriber_id = (int) $subscriber_result;

( new WP_User( $administrator_id ) )->set_role( 'administrator' );
( new WP_User( $subscriber_id ) )->set_role( 'subscriber' );

wp_set_current_user( $administrator_id );
wpup_smoke_assert( current_user_can( 'edit_user', $subscriber_id ), 'Administrator cannot edit subscriber.' );
wpup_smoke_assert( false !== strpos( get_edit_user_link( $subscriber_id ), 'page=profile' ), 'Filtered edit link does not use a profile section.' );

wp_set_current_user( $subscriber_id );
wpup_smoke_assert( array( 'exist' ) === wp_user_profiles_map_meta_cap( array( 'edit_users' ), 'edit_profile', $subscriber_id, array( $subscriber_id ) ), 'Self-profile capability did not map to exist.' );
wpup_smoke_assert( ! current_user_can( 'edit_user', $administrator_id ), 'Subscriber can edit another user.' );

echo "WP User Profiles single-site smoke test passed.\n";
