<?php

/**
 * User Profile Personal-Options Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/PersonalOptions
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
function wp_user_profiles_personal_options_metabox( $user = null ) {

	// Start a buffer
	ob_start();

	?><table class="form-table">

		<tr class="user-rich-editing-wrap">
			<th scope="row"><?php esc_html_e( 'Visual Editor', 'wp-user-profiles' ); ?></th>
			<td>
				<label for="rich_editing"><input name="rich_editing" type="checkbox" id="rich_editing" value="false" <?php checked( 'false', $user->rich_editing ); ?> />
					<?php esc_html_e( 'Disable the visual editor when writing', 'wp-user-profiles' ); ?>
				</label>
			</td>
		</tr><?php

		// Only show if user can moderate comments
		if ( user_can( $user->ID, 'moderate_comments' ) ) :

			?><tr class="user-comment-shortcuts-wrap">
				<th scope="row"><?php esc_html_e( 'Keyboard Shortcuts', 'wp-user-profiles' ); ?></th>
				<td>
					<label for="comment_shortcuts"><input type="checkbox" name="comment_shortcuts" id="comment_shortcuts" value="true" <?php checked( 'true', $user->comment_shortcuts ); ?> />
						<?php esc_html_e( 'Enable keyboard shortcuts for comment moderation.', 'wp-user-profiles' ); ?>
					</label>
				</td>
			</tr><?php

		endif;

		// Only show setting if admin var can be visible
		if ( apply_filters( 'show_admin_bar', true ) ) :

			?><tr class="show-admin-bar user-admin-bar-front-wrap">
				<th scope="row"><?php esc_html_e( 'Toolbar', 'wp-user-profiles' ); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e( 'Toolbar', 'wp-user-profiles' ) ?></span></legend>
						<label for="admin_bar_front">
							<input name="admin_bar_front" type="checkbox" id="admin_bar_front" value="1" <?php checked( _get_admin_bar_pref( 'front', $user->ID ) ); ?> />
							<?php esc_html_e( 'Show Toolbar when viewing site', 'wp-user-profiles' ); ?>
						</label>
					</fieldset>
				</td>
			</tr><?php

		endif;

		/**
		 * Fires at the end of the 'Personal Options' settings table on the user editing screen.
		 *
		 * @since 2.7.0
		 *
		 * @param WP_User $user The current WP_User object.
		 */
		do_action( 'personal_options', $user );

	?></table><?php

	// Output contents of buffer
	ob_end_flush();
}
