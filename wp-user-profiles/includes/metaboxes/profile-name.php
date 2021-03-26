<?php

/**
 * User Profile Name Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Name
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the personal options metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_name_metabox( $user = null ) {

	// Before
	do_action( __FUNCTION__ . '_before', $user );

	// Default array
	$public_display = array();

	// Nick & User
	$public_display['display_nickname']  = $user->nickname;
	$public_display['display_username']  = $user->user_login;

	// First
	if ( ! empty( $user->first_name ) ) {
		$public_display['display_firstname'] = $user->first_name;
	}

	// Last
	if ( ! empty( $user->last_name ) ) {
		$public_display['display_lastname'] = $user->last_name;
	}

	// Combined
	if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
		$public_display['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
		$public_display['display_lastfirst'] = $user->last_name  . ' ' . $user->first_name;
	}

	// Display
	if ( ! in_array( $user->display_name, $public_display ) ) {
		$public_display = array( 'display_displayname' => $user->display_name ) + $public_display;
	}

	// Trim & tidy
	$public_display = array_unique( array_map( 'trim', $public_display ) );

	?><table class="form-table">
		<tr class="user-user-login-wrap">
			<th scope="row">
				<label for="user_login"><?php esc_html_e( 'Username', 'wp-user-profiles' ); ?></label>
			</th>
			<td>
				<cite title="<?php esc_html_e( 'Usernames cannot be changed.', 'wp-user-profiles' ); ?>"><?php echo esc_attr( $user->user_login ); ?></cite>
				<input type="hidden" name="user_login" id="user_login" value="<?php echo esc_attr( $user->user_login ); ?>" disabled="disabled" class="regular-text" />
			</td>
		</tr>

		<tr class="user-first-name-wrap">
			<th scope="row">
				<label for="first_name"><?php esc_html_e( 'First Name', 'wp-user-profiles' ); ?></label>
			</th>
			<td>
				<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $user->first_name ); ?>" class="regular-text" />
			</td>
		</tr>

		<tr class="user-last-name-wrap">
			<th scope="row">
				<label for="last_name"><?php esc_html_e( 'Last Name', 'wp-user-profiles' ); ?></label>
			</th>
			<td>
				<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $user->last_name ); ?>" class="regular-text" />
			</td>
		</tr>

		<tr class="user-nickname-wrap">
			<th scope="row">
				<label for="nickname"><?php esc_html_e( 'Nickname', 'wp-user-profiles' ); ?>
					<span class="description"><?php esc_html_e( '(required)', 'wp-user-profiles' ); ?></span>
				</label>
			</th>
			<td>
				<input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $user->nickname ) ?>" class="regular-text" />
			</td>
		</tr>

		<tr class="user-display-name-wrap">
			<th scope="row">
				<label for="display_name"><?php esc_html_e( 'Display name publicly as', 'wp-user-profiles' ); ?></label>
			</th>
			<td>
				<select name="display_name" id="display_name"><?php

					// Show options
					foreach ( $public_display as $item ) :

						?><option <?php selected( $user->display_name, $item ); ?>><?php echo esc_html( $item ); ?></option><?php

					endforeach;

				?></select>
			</td>
		</tr>
	</table>

	<?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
