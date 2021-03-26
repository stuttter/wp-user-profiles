<?php

/**
 * User Profile Email Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Email
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the contact metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_email_metabox( $user = null ) {
	$current_user = wp_get_current_user();
	$new_email    = get_option( $current_user->ID . '_new_email' );

	// Before
	do_action( __FUNCTION__ . '_before', $user ); ?>

	<table class="form-table">
		<tr class="user-email-wrap">
			<th scope="row">
				<label for="email"><?php esc_html_e( 'Email', 'wp-user-profiles' ); ?>
					<span class="description"><?php esc_html_e( '(required)', 'wp-user-profiles' ); ?></span>
				</label>
			</th>
			<td>
				<input type="email" name="email" id="email" value="<?php echo esc_attr( $user->user_email ) ?>" class="regular-text ltr" />

				<?php if ( $new_email && $new_email['newemail'] !== $current_user->user_email && $user->ID === $current_user->ID ) : ?>

					<div class="updated inline">
					<p><?php
						printf(
							__( 'There is a pending change of your email to %1$s. <a href="%2$s">Cancel</a>', 'wp-user-profiles' ),
							'<code>' . esc_html( $new_email['newemail'] ) . '</code>',
							esc_url( self_admin_url( 'profile.php?dismiss=' . $current_user->ID . '_new_email' ) )
					); ?></p>
					</div>

				<?php endif; ?>

			</td>
		</tr>
	</table>

	<?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
