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

			?><tr class="user-<?php echo esc_attr( $name ); ?>-wrap">
				<th scope="row">
					<label for="<?php echo esc_attr( $name ); ?>"><?php

						// This filter documented in wp-admin/user-edit.php
						echo apply_filters( "user_{$name}_label", $desc );

					?></label>
				</th>
				<td>
					<input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $user->{$name} ); ?>" class="regular-text" />
				</td>
			</tr><?php

		endforeach;

	?></table>

	<?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
