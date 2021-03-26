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

	// Before
	do_action( __FUNCTION__ . '_before', $user ); ?>

	<table class="form-table">
		<tr class="user-admin-color-wrap">
			<th scope="row"><?php esc_html_e( 'Admin Color Scheme', 'wp-user-profiles' ); ?></th>
			<td><?php

				// This action documented in wp-admin/user-edit.php
				do_action( 'admin_color_scheme_picker', $user->ID );

			?></td>
		</tr>
	</table>

	<?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
