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
wpup_smoke_assert( ! is_multisite(), 'Single-site smoke test cannot run on multisite.' );

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

// Exercise the real Core callback before the plugin's Account-section save.
$subscriber      = get_userdata( $subscriber_id );
$original_email  = $subscriber->user_email;
$requested_email = "wpup-new-{$run_id}@example.test";
$_POST           = array(
	'user_id' => $subscriber_id,
	'email'   => $requested_email,
);

add_filter( 'pre_wp_mail', '__return_true' );
send_confirmation_on_profile_email( $subscriber_id );
remove_filter( 'pre_wp_mail', '__return_true' );

$pending_email = get_user_meta( $subscriber_id, '_new_email', true );
wpup_smoke_assert( $requested_email === $pending_email['newemail'], 'Core did not record the pending email change.' );
wpup_smoke_assert( isset( $_POST['email'] ) && $original_email === $_POST['email'], 'Core did not restore the current email before the plugin save.' );

$account_section = null;
foreach ( wp_user_profiles_sections() as $section ) {
	if ( 'account' === $section->id ) {
		$account_section = $section;
		break;
	}
}

wpup_smoke_assert( $account_section instanceof WP_User_Profile_Account_Section, 'Account section was not registered.' );
$save_result = $account_section->save( get_userdata( $subscriber_id ) );
wpup_smoke_assert( ! is_wp_error( $save_result ), 'Account section rejected the Core-managed email change.' );
wpup_smoke_assert( get_userdata( $subscriber_id )->user_email === $original_email, 'Pending email became active before confirmation.' );

// The plugin yields matching cancellation requests to profile.php; Core still
// owns and enforces the nonce before deleting the pending change.
$dismiss_action = 'dismiss-' . $subscriber_id . '_new_email';
$_GET           = array( 'dismiss' => $subscriber_id . '_new_email' );
$_REQUEST       = array( '_wpnonce' => wp_create_nonce( $dismiss_action ) );
wpup_smoke_assert( null === wp_user_profiles_old_profile_redirect(), 'Plugin intercepted a valid Core cancellation route.' );
wpup_smoke_assert( 1 === check_admin_referer( $dismiss_action ), 'Core rejected a valid cancellation nonce.' );

$_REQUEST       = array();
$nonce_rejected = false;
$die_handler    = static function () {
	return static function () {
		throw new RuntimeException( 'Core rejected the cancellation nonce.' );
	};
};
add_filter( 'wp_die_handler', $die_handler );

try {
	wpup_smoke_assert( null === wp_user_profiles_old_profile_redirect(), 'Plugin intercepted an invalid Core cancellation route.' );
	check_admin_referer( $dismiss_action );
} catch ( RuntimeException $exception ) {
	$nonce_rejected = true;
}

remove_filter( 'wp_die_handler', $die_handler );
wpup_smoke_assert( $nonce_rejected, 'Core accepted a missing cancellation nonce.' );
$pending_email = get_user_meta( $subscriber_id, '_new_email', true );
wpup_smoke_assert( is_array( $pending_email ) && $requested_email === $pending_email['newemail'], 'Pending email was removed without a valid nonce.' );

delete_user_meta( $subscriber_id, '_new_email' );
$_GET     = array();
$_POST    = array();
$_REQUEST = array();

echo "WP User Profiles single-site smoke test passed.\n";
