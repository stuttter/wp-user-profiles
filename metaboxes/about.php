<?php

/**
 * User Profile About Metabox
 * 
 * @package User/Profiles/Metaboxes/About
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
?>

	<table class="form-table">
		<tr class="user-url-wrap">
			<th><label for="url"><?php _e('Website') ?></label></th>
			<td><input type="url" name="url" id="url" value="<?php echo esc_attr( $user->user_url ) ?>" class="regular-text code" /></td>
		</tr>
		<tr class="user-description-wrap">
			<th><label for="description"><?php _e('Biographical Info'); ?></label></th>
			<td>
				<textarea name="description" id="description" rows="5" cols="30"><?php echo $user->description; // textarea_escaped ?></textarea>
				<p class="description">
					<?php _e('Share a little biographical information to fill out your profile. This may be shown publicly.'); ?></p>
			</td>
		</tr>
	</table>

	<?php
}
