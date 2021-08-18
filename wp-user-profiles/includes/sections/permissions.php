<?php

/**
 * User Profile Permissions Section
 *
 * @package Plugins/Users/Profiles/Sections/Permissions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * User Profiles "Permissions" class
 *
 * @since 0.2.0
 */
class WP_User_Profile_Permissions_Section extends WP_User_Profile_Section {

	/**
	 * Add the meta boxes for this section
	 *
	 * @since 0.2.0
	 *
	 * @param string $type
	 * @param array  $args
	 */
	public function add_meta_boxes( $type = '', $args = array() ) {

		// Allow third party plugins to add metaboxes
		parent::add_meta_boxes( $type, $args );

		// Roles
		add_meta_box(
			'roles',
			_x( 'Roles', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_roles_metabox',
			$type,
			'normal',
			'high',
			$args
		);

		// Additional Capabilities
		add_meta_box(
			'options',
			_x( 'Additional Capabilities', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_additional_capabilities_metabox',
			$type,
			'normal',
			'core',
			$args
		);
	}

	/**
	 * Save section data
	 *
	 * @since 0.2.0
	 *
	 * @param WP_User $user
	 * @return mixed Integer on success. WP_Error on failure.
	 */
	public function save( $user = null ) {

		// Role changes
		if ( isset( $_POST['role'] ) && is_array( $_POST['role'] ) && current_user_can( $this->cap, $user->ID ) ) {

			// Stash the current Site ID for later reuse
			$current_site_id = get_current_blog_id();

			// Loop through new roles
			foreach ( $_POST['role'] as $site_id => $new_role ) {

				// Switch to the blog
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

				// Get roles for this site
				$editable_roles = get_editable_roles();

				// Only allow editable roles for switched site
				if ( ! empty( $new_role ) && ! empty( $editable_roles[ $new_role ] ) ) {
					$user->set_role( $new_role );

				// ...or remove all caps if no role for site
				} elseif ( empty( $new_role ) ) {
					$user->remove_all_caps();
				}

				// Switch back to the current site
				if ( is_multisite() ) {
					restore_current_blog();

					// Reset the user's role & capabilities
					$user->for_site( $current_site_id );
				}
			}
		}

		// Allow third party plugins to save data in this section
		return parent::save( $user );
	}

	/**
	 * Contextual help for this section
	 *
	 * @since 0.2.0
	 */
	public function add_contextual_help() {
		get_current_screen()->add_help_tab( array(
			'id'		=> $this->id,
			'title'		=> $this->name,
			'content'	=>
				'<p>'  . esc_html__( 'This is where role & capability settings can be found.', 'wp-user-profiles' ) . '</p><ul>' .
				'<li>' . esc_html__( 'Your role determines what you are able to do',           'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'In some cases, you may have more than one role',         'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'Some capabilities may be uniquely granted',              'wp-user-profiles' ) . '</li></ul>'
		) );
	}
}
