<?php

/**
 * User Profile Capabilities Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Capabilities
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the capabilities metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_additional_capabilities_metabox( $user = null ) {

	// Get the roles global
	$wp_roles = new WP_Roles();?>

	<table class="form-table">

		<?php if ( is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) && ! isset( $GLOBALS['super_admins'] ) ) : ?>

			<tr class="user-super-admin-wrap"><th><?php esc_html_e( 'Super Admin', 'wp-user-profiles' ); ?></th>
				<td>

					<?php if ( $user->user_email !== get_site_option( 'admin_email' ) || ! is_super_admin( $user->ID ) ) : ?>

						<p>
							<label>
								<input type="checkbox" id="super_admin" name="super_admin"<?php checked( is_super_admin( $user->ID ) ); ?> />
								<?php esc_html_e( 'Grant this user super admin privileges for the Network.', 'wp-user-profiles' ); ?>
							</label>
						</p>

					<?php else : ?>

						<p><?php esc_html_e( 'Super admin privileges cannot be removed because this user has the network admin email.', 'wp-user-profiles' ); ?></p>

					<?php endif; ?>

				</td>
			</tr>

		<?php endif; ?>

		<tr class="user-capabilities-wrap">
			<th scope="row"><?php esc_html_e( 'Capabilities', 'wp-user-profiles' ); ?></th>
			<td>
				<?php
					$output = '';
					foreach ( $user->caps as $cap => $value ) {
						if ( ! $wp_roles->is_role( $cap ) ) {
							if ( '' !== $output ) {
								$output .= ', ';
							}

							$output .= $value
								? sprintf( esc_html__( 'Allowed: %s', 'wp-user-profiles' ), $cap )
								: sprintf( esc_html__( 'Denied: %s',  'wp-user-profiles' ), $cap );
						}
					}

					if ( ! empty( $output ) ) {
						echo $output;
					} else {
						esc_html_e( 'No additional capabilities', 'wp-user-profiles' );
					}
				?>
			</td>
		</tr>
	</table>

<?php
}
