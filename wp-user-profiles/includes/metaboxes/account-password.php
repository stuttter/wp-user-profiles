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
function wp_user_profiles_password_metabox() {
?>

	<table class="form-table">
		<tr id="password" class="user-pass1-wrap">
			<th><label for="pass1"><?php esc_html_e( 'New Password', 'wp-user-profiles' ); ?></label></th>
			<td>
				<input class="hidden" value=" " /><!-- #24364 workaround -->
				<button type="button" class="button button-secondary wp-generate-pw hide-if-no-js"><?php esc_html_e( 'Generate Password', 'wp-user-profiles' ); ?></button>
				<div class="wp-pwd hide-if-js">
					<span class="password-input-wrapper">
						<input type="password" name="pass1" id="pass1" class="regular-text" value="" autocomplete="off" data-pw="<?php echo esc_attr( wp_generate_password( 24 ) ); ?>" aria-describedby="pass-strength-result" />
					</span>
					<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password' ); ?>">
						<span class="dashicons dashicons-hidden"></span>
						<span class="text"><?php esc_html_e( 'Hide', 'wp-user-profiles' ); ?></span>
					</button>
					<button type="button" class="button button-secondary wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Cancel password change' ); ?>">
						<span class="text"><?php esc_html_e( 'Cancel', 'wp-user-profiles' ); ?></span>
					</button>
					<div style="display:none" id="pass-strength-result" aria-live="polite"></div>
				</div>
			</td>
		</tr>
		<tr class="user-pass2-wrap hide-if-js">
			<th scope="row"><label for="pass2"><?php esc_html_e( 'Repeat New Password', 'wp-user-profiles' ); ?></label></th>
			<td>
				<input name="pass2" type="password" id="pass2" class="regular-text" value="" autocomplete="off" />
				<p class="description"><?php esc_html_e( 'Type your new password again.', 'wp-user-profiles' ); ?></p>
			</td>
		</tr>
		<tr class="pw-weak">
			<th><?php esc_html_e( 'Confirm Password', 'wp-user-profiles' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="pw_weak" class="pw-checkbox" />
					<?php esc_html_e( 'Confirm use of weak password', 'wp-user-profiles' ); ?>
				</label>
			</td>
		</tr>
	</table>

<?php
}
