<?php

/**
 * User Switching Integration
 *
 * @package Plugins/Users/Profiles/UserSwitching
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add "Switch to User" button to User Profile screen
 *
 * This function integrates with the User Switching plugin to provide
 * a convenient link to switch to the user being viewed.
 *
 * @since 2.7.0
 *
 * @param WP_User $user The WP_User object being edited.
 */
function wp_user_profiles_user_switching_link( $user = null ) {

	// Determine the User Switching class name (support both versions)
	if ( class_exists( 'User_Switching' ) ) {
		$user_switching_class = 'User_Switching';
	} elseif ( class_exists( 'user_switching' ) ) {
		$user_switching_class = 'user_switching';
	} else {
		// User Switching plugin is not active
		return;
	}

	// Bail if required methods don't exist
	if ( ! method_exists( $user_switching_class, 'switch_to_url' ) ) {
		return;
	}

	// Bail if no user
	if ( empty( $user ) || empty( $user->ID ) ) {
		return;
	}

	// Bail if current user cannot switch to this user
	if ( ! current_user_can( 'switch_to_user', $user->ID ) ) {
		return;
	}

	// Get current URL for redirect
	if ( method_exists( $user_switching_class, 'current_url' ) ) {
		$current_url = call_user_func( array( $user_switching_class, 'current_url' ) );
		// Ensure we got a valid URL string
		if ( empty( $current_url ) || ! is_string( $current_url ) ) {
			$current_url = get_edit_user_link( $user->ID );
		}
	} else {
		// Fallback to the current user profile page
		$current_url = get_edit_user_link( $user->ID );
	}

	// Validate the redirect URL
	$redirect_to = wp_validate_redirect( $current_url, admin_url() );
	if ( false === $redirect_to ) {
		$redirect_to = admin_url();
	}

	// Get the switch URL
	$switch_url = call_user_func( array( $user_switching_class, 'switch_to_url' ), $user );

	// Bail if we didn't get a valid switch URL string
	if ( empty( $switch_url ) || ! is_string( $switch_url ) ) {
		return;
	}

	// Add redirect parameter to switch URL
	$url = add_query_arg(
		array(
			'redirect_to' => $redirect_to,
		),
		$switch_url
	);

	?>
	<div class="submitbox" id="user-switching-box">
		<div id="user-switching-actions">
			<div id="user-switching-action">
				<a href="<?php echo esc_url( $url ); ?>" class="button"><?php esc_html_e( 'Switch To', 'wp-user-profiles' ); ?></a>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<?php
}
