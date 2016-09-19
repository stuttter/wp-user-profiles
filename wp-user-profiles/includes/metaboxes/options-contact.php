<?php

/**
 * User Profile Contact Metabox
 * 
 * @package Plugins/Users/Profiles/Metaboxes/Contact
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the contact metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_contact_metabox( $user = null ) {

	// Get methods
	$methods = wp_get_user_contact_methods( $user ); ?>

	<table class="form-table">

		<?php foreach ( $methods as $name => $desc ) : ?>

			<tr class="user-<?php echo esc_attr( $name ); ?>-wrap">
				<th>
					<label for="<?php echo esc_attr( $name ); ?>">
						<?php
						/**
						 * Filter a user contactmethod label.
						 *
						 * The dynamic portion of the filter hook, `$name`, refers to
						 * each of the keys in the contactmethods array.
						 *
						 * @since 2.9.0
						 *
						 * @param string $desc The translatable label for the contactmethod.
						 */
						echo apply_filters( "user_{$name}_label", $desc ); ?>
					</label>
				</th>
				<td><input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $user->$name ); ?>" class="regular-text" /></td>
			</tr>

		<?php endforeach; ?>

	</table>

	<?php
}
