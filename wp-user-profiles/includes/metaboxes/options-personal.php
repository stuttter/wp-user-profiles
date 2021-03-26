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
	global $user_can_edit;

	// Start a buffer
	ob_start();

	// Before
	do_action( __FUNCTION__ . '_before', $user );

	// Whether to show specific options
	$show = array(
		'visual_editor'       => ! empty( $user_can_edit ),
		'keyboard_shortcuts'  => ! empty( $user_can_edit ),
		'admin_bar'           => apply_filters( 'show_admin_bar', true ),
		'syntax_highlighting' => (

			// For Custom HTML widget and Additional CSS in Customizer.
			user_can( $user, 'edit_theme_options' )
			||
			// Edit plugins.
			user_can( $user, 'edit_plugins' )
			||
			// Edit themes.
			user_can( $user, 'edit_themes' )
		),

		// Third-party plugins
		'personal_options'    => wp_user_profiles_buffer_action( 'personal_options', $user )
	);

	?><table class="form-table"><?php

		// Visual Editor
		if ( ! empty( $show['visual_editor'] ) ) :

			?><tr class="user-rich-editing-wrap">
				<th scope="row"><?php esc_html_e( 'Visual Editor', 'wp-user-profiles' ); ?></th>
				<td>
					<label for="rich_editing"><input name="rich_editing" type="checkbox" id="rich_editing" value="false" <?php checked( 'false', $user->rich_editing ); ?> />
						<?php esc_html_e( 'Disable the visual editor when writing', 'wp-user-profiles' ); ?>
					</label>
				</td>
			</tr><?php

		endif;

		// Syntax Highlighting
		if ( ! empty( $show['syntax_highlighting'] ) ) :

			?><tr class="user-syntax-highlighting-wrap">
				<th scope="row"><?php esc_html_e( 'Syntax Highlighting', 'wp-user-profiles' ); ?></th>
				<td>
					<label for="syntax_highlighting"><input name="syntax_highlighting" type="checkbox" id="syntax_highlighting" value="false" <?php checked( 'false', $user->syntax_highlighting ); ?> />
						<?php esc_html_e( 'Disable syntax highlighting when editing code', 'wp-user-profiles' ); ?>
					</label>
				</td>
			</tr><?php

		endif;

		// Keyboard Shortcuts
		if ( ! empty( $show['keyboard_shortcuts'] ) ) :

			?><tr class="user-comment-shortcuts-wrap">
				<th scope="row"><?php esc_html_e( 'Keyboard Shortcuts', 'wp-user-profiles' ); ?></th>
				<td>
					<label for="comment_shortcuts"><input type="checkbox" name="comment_shortcuts" id="comment_shortcuts" value="true" <?php checked( 'true', $user->comment_shortcuts ); ?> />
						<?php esc_html_e( 'Enable keyboard shortcuts for comment moderation.', 'wp-user-profiles' ); ?>
					</label>
				</td>
			</tr><?php

		endif;

		// Admin Bar
		if ( ! empty( $show['admin_bar'] ) ) :

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

		// Third-party Personal Options
		if ( ! empty( $show['personal_options'] ) ) :
			echo $show['personal_options'];
		endif;

		// Maybe empty
		wp_user_profiles_handle_empty_metabox( $show );

	?></table><?php

	// Output third-party profile personal options
	if ( wp_is_profile_page() ) {
		echo wp_user_profiles_buffer_action( 'profile_personal_options', $user );
	}

	// After
	do_action( __FUNCTION__ . '_after', $user );

	// Output contents of buffer
	ob_end_flush();
}
