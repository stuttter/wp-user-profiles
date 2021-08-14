<?php

/**
 * User Profile Password Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Password
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the password metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_password_metabox( $user = null ) {

	// Before
	do_action( __FUNCTION__ . '_before', $user ); ?>

	<table class="form-table">
		<tr id="password" class="user-pass1-wrap">
			<th scope="row"><label for="pass1"><?php esc_html_e( 'New Password', 'wp-user-profiles' ); ?></label></th>
			<td>
				<input class="hidden" value=" " /><!-- #24364 workaround -->
				<button type="button" class="button wp-generate-pw hide-if-no-js" aria-expanded="false"><?php esc_html_e( 'Set New Password', 'wp-user-profiles' ); ?></button>
				<div class="wp-pwd hide-if-js">
					<span class="password-input-wrapper">
						<input type="password" name="pass1" id="pass1" class="regular-text" value="" autocomplete="off" data-pw="<?php echo esc_attr( wp_generate_password( 24 ) ); ?>" aria-describedby="pass-strength-result" />
					</span>
					<button type="button" class="button wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password', 'wp-user-profiles' ); ?>">
						<span class="dashicons dashicons-hidden" aria-hidden="true"></span>
						<span class="text"><?php esc_html_e( 'Hide', 'wp-user-profiles' ); ?></span>
					</button>
					<button type="button" class="button wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Cancel', 'wp-user-profiles' ); ?>">
						<span class="dashicons dashicons-no" aria-hidden="true"></span>
						<span class="text"><?php esc_html_e( 'Cancel', 'wp-user-profiles' ); ?></span>
					</button>
					<div style="display:none" id="pass-strength-result" aria-live="polite"></div>
				</div>
			</td>
		</tr>
		<tr class="user-pass2-wrap hide-if-js">
			<th scope="row"><label for="pass2"><?php esc_html_e( 'Repeat New Password', 'wp-user-profiles' ); ?></label></th>
			<td>
				<input name="pass2" type="password" id="pass2" class="regular-text" value="" autocomplete="off" aria-describedby="pass2-desc" />
				<?php if ( IS_PROFILE_PAGE ) : ?>
					<p class="description" id="pass2-desc"><?php esc_html_e( 'Type your new password again.', 'wp-user-profiles' ); ?></p>
				<?php else : ?>
					<p class="description" id="pass2-desc"><?php esc_html_e( 'Type the new password again.', 'wp-user-profiles' ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr class="pw-weak">
			<th scope="row"><?php esc_html_e( 'Confirm Password', 'wp-user-profiles' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="pw_weak" class="pw-checkbox" />
					<span id="pw-weak-text-label"><?php esc_html_e( 'Confirm use of weak password', 'wp-user-profiles' ); ?></span>
				</label>
			</td>
		</tr>

		<?php if ( ! wp_is_profile_page() && function_exists( 'retrieve_password' ) ) : ?>

			<tr class="user-generate-reset-link-wrap hide-if-no-js">
				<th scope="row"><?php esc_html_e( 'Password Reset', 'wp-user-profiles' ); ?></th>
				<td>
					<div class="generate-reset-link">
						<button type="button" class="button button-secondary" id="generate-reset-link">
							<?php esc_html_e( 'Send Password Reset Email', 'wp-user-profiles' ); ?>
						</button>
					</div>
					<p class="description">
						<?php esc_html_e( 'Sending this email does not force-change their password.', 'wp-user-profiles' ); ?>
					</p>
				</td>
			</tr>

		<?php endif; ?>

	</table>

	<?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
