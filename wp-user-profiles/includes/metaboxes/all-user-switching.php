<?php

/**
 * User Switching Integration
 *
 * @package Plugins/Users/Profiles/Metaboxes/UserSwitching
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

	// Bail if User Switching plugin is not active
	if ( ! function_exists( 'switch_to_user' ) ) {
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

	// Get current URL and validate it for redirect
	$current_url = user_switching::current_url();
	$redirect_to = wp_validate_redirect( $current_url, admin_url() );

	// Get the switch URL with redirect back to current page
	$url = add_query_arg(
		array(
			'redirect_to' => rawurlencode( $redirect_to ),
		),
		user_switching::switch_to_url( $user )
	);

	?>
	<div class="submitbox">
		<div id="major-publishing-actions">
			<div id="publishing-action">
				<a href="<?php echo esc_url( $url ); ?>" class="button"><?php esc_html_e( 'Switch To', 'wp-user-profiles' ); ?></a>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<?php
}
