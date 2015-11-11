<?php

/**
 * User Profile Color-Scheme Metabox
 * 
 * @package Plugins/Users/Profiles/Metaboxes/ColorScheme
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
function wp_user_profiles_color_scheme_metabox( $user = null ) {
?>

	<table class="form-table">
		<tr class="user-admin-color-wrap">
			<th scope="row"><?php esc_html_e( 'Admin Color Scheme', 'wp-user-profiles' ); ?></th>
			<td><?php
			/**
			 * Fires in the 'Admin Color Scheme' section of the user editing screen.
			 *
			 * The section is only enabled if a callback is hooked to the action,
			 * and if there is more than one defined color scheme for the admin.
			 *
			 * @since 3.0.0
			 * @since 3.8.1 Added `$user_id` parameter.
			 *
			 * @param int $user_id The user ID.
			 */
			do_action( 'admin_color_scheme_picker', $user->ID );
			?></td>
		</tr>
	</table>

	<?php
}
