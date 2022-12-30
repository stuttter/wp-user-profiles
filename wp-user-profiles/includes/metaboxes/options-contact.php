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
	$methods = wp_get_user_contact_methods( $user );

	// Before
	do_action( __FUNCTION__ . '_before', $user ); ?>

	<table class="form-table"><?php

		foreach ( $methods as $name => $desc ) :

			$row_class = "user-{$name}-wrap";

			?><tr class="<?php echo esc_attr( $row_class ); ?>">
				<th scope="row">
					<label for="<?php echo esc_attr( $name ); ?>"><?php

						// This filter documented in wp-admin/user-edit.php
						echo apply_filters( "user_{$name}_label", $desc );

					?></label>
				</th>
				<td>
					<input
						type="text"
						class="regular-text"
						id="<?php echo esc_attr( $name ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						value="<?php echo esc_attr( $user->{$name} ); ?>"
					/>
				</td>
			</tr><?php

		endforeach;

	?></table>

	<?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
