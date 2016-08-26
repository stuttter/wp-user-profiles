<?php

/**
 * User Profile About Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/About
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the about metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_about_metabox( $user = null ) {

	// Start
	ob_start();

	// Before
	do_action( 'wp_user_profiles_about_metabox_before', $user );

	?><table class="form-table">
		<tr class="user-url-wrap">
			<th><label for="url"><?php esc_html_e( 'Website', 'wp-user-profiles' ) ?></label></th>
			<td><input type="url" name="url" id="url" value="<?php echo esc_attr( $user->user_url ) ?>" class="regular-text code" /></td>
		</tr>
		<tr class="user-description-wrap">
			<th><label for="description"><?php esc_html_e( 'Biographical Info', 'wp-user-profiles' ); ?></label></th>
			<td>
				<?php
				wp_editor( $user->description, 'description',
					array(
						'media_buttons' => false,
						'quicktags'     => false,
						'tinymce'       => array(
							'toolbar1' => 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,spellchecker,fullscreen,wp_adv',
							'toolbar2' => 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help'
						)
					)
				);
				?>
				<p class="description">
					<?php esc_html_e( 'Share a little biographical information to fill out your profile. This may be shown publicly.', 'wp-user-profiles' ); ?>
				</p>
			</td>
		</tr>
	</table><?php

	// After
	do_action( 'wp_user_profiles_about_metabox_after', $user );

	// End
	ob_end_flush();
}
