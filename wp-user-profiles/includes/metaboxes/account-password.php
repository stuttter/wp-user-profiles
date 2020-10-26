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
			<th><label for="pass1"><?php _e( 'New Password' ); ?></label></th>
			<td>
				<input class="hidden" value=" " /><!-- #24364 workaround -->
				<button type="button" class="button wp-generate-pw hide-if-no-js" aria-expanded="false"><?php _e( 'Set New Password' ); ?></button>
				<div class="wp-pwd hide-if-js">
					<span class="password-input-wrapper">
						<input type="password" name="pass1" id="pass1" class="regular-text" value="" autocomplete="off" data-pw="<?php echo esc_attr( wp_generate_password( 24 ) ); ?>" aria-describedby="pass-strength-result" />
					</span>
					<button type="button" class="button wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password' ); ?>">
						<span class="dashicons dashicons-hidden" aria-hidden="true"></span>
						<span class="text"><?php _e( 'Hide' ); ?></span>
					</button>
					<button type="button" class="button wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Cancel' ); ?>">
						<span class="dashicons dashicons-no" aria-hidden="true"></span>
						<span class="text"><?php _e( 'Cancel' ); ?></span>
					</button>
					<div style="display:none" id="pass-strength-result" aria-live="polite"></div>
				</div>
			</td>
		</tr>
		<tr class="user-pass2-wrap hide-if-js">
			<th scope="row"><label for="pass2"><?php _e( 'Repeat New Password' ); ?></label></th>
			<td>
				<input name="pass2" type="password" id="pass2" class="regular-text" value="" autocomplete="off" aria-describedby="pass2-desc" />
				<?php if ( IS_PROFILE_PAGE ) : ?>
					<p class="description" id="pass2-desc"><?php _e( 'Type your new password again.' ); ?></p>
				<?php else : ?>
					<p class="description" id="pass2-desc"><?php _e( 'Type the new password again.' ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr class="pw-weak">
			<th><?php _e( 'Confirm Password' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="pw_weak" class="pw-checkbox" />
					<span id="pw-weak-text-label"><?php _e( 'Confirm use of weak password' ); ?></span>
				</label>
			</td>
		</tr>
	</table>

	<?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
