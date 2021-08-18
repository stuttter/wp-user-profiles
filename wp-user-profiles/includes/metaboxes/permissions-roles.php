<?php

/**
 * User Profile Roles Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Roles
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
function wp_user_profiles_roles_metabox( $user = null ) {

	// Should dropdown be disabled
	$is_self = wp_is_profile_page();

	// Stash the current Site ID for later reuse
	$current_site_id = get_current_blog_id();

	// When viewing blog admin, only show roles for that blog
	if ( is_blog_admin() ) {

		// This part is pretty backwards
		$sites = is_multisite()
			? array( get_blog_details( $current_site_id ) )
			: get_blogs_of_user( $user->ID, true );

	// Show all sites when not in blog admin
	} else {
		$sites = get_blogs_of_user( $user->ID, true );
	}

	// Before
	do_action( __FUNCTION__ . '_before', $user ); ?>

	<table class="form-table"><?php

		// User is not a member of any sites
		if ( empty( $sites ) ) :

			?><tr class="user-role-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'No Sites', 'wp-user-profiles' ); ?></label>
				</th>
				<td><?php

					// Switch up verbiage based on current user
					( true === $is_self )
						? esc_html_e( 'You have no role on any sites.',     'wp-user-profiles' )
						: esc_html_e( 'This user has no role on any sites', 'wp-user-profiles' );

				?></td>
			</tr><?php

		// User is member of some sites
		else :
			foreach ( $sites as $site_id => $site ) :

				// Cast for strict checks
				$site_id = absint( $site_id );

				// Switch to this site
				if ( is_multisite() ) {

					// Switch site early
					switch_to_blog( $site_id );

					// User cannot be promoted on this site by current user
					if ( ( $current_site_id !== $site_id ) && ! current_user_can( 'promote_user', $user->ID ) ) {

						// Switch site back
						restore_current_blog();

						// Skip to next site
						continue;
					}

					// Reinitialize the user roles & caps for this site ID
					$user->for_site( $site_id );
				}

				// Compare user role against currently editable roles
				$editable_roles = get_editable_roles();
				$user_roles     = array_intersect( array_values( $user->roles ), array_keys( $editable_roles ) );
				$user_role      = reset( $user_roles );

				?>

				<tr class="user-role-wrap">
					<th scope="row">
						<label for="role[<?php echo (int) $site_id; ?>]">
							<?php echo esc_html( $site->blogname ); ?><br>
							<span class="description"><?php echo esc_html( $site->siteurl ); ?></span>
						</label>
					</th>
					<td><select name="role[<?php echo (int) $site_id; ?>]" id="role[<?php echo (int) $site_id; ?>]" <?php disabled( $is_self, true ); ?>><?php

							// Print the full list of roles
							wp_dropdown_roles( $user_role );

							?><option value="" <?php selected( empty( $user_role ) ); ?>><?php esc_html_e( '&mdash; No role for this site &mdash;', 'wp-user-profiles' ); ?></option>
						</select>
					</td>
				</tr>

				<?php

				// Switch back to the current site
				if ( is_multisite() ) {
					restore_current_blog();

					// Reset the user's role & capabilities
					$user->for_site( $current_site_id );
				}

			endforeach;

		endif;

	?></table>

	<?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
