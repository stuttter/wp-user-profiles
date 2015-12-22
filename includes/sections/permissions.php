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
	 * @param object $user
	 */
	public function add_meta_boxes( $type = '', $user = null ) {

		// Allow third party plugins to add metaboxes
		parent::add_meta_boxes( $type, $user );

		// Roles
		add_meta_box(
			'roles',
			_x( 'Roles', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_roles_metabox',
			$type,
			'normal',
			'high',
			$user
		);

		// Additional Capabilities
		add_meta_box(
			'options',
			_x( 'Additional Capabilities', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_additional_capabilities_metabox',
			$type,
			'normal',
			'core',
			$user
		);
	}

	/**
	 * Save section data
	 *
	 * @since 0.2.0
	 *
	 * @param WP_User $user
	 */
	public function save( $user = null ) {

		// Role changes
		if ( isset( $_POST['role'] ) && is_array( $_POST['role'] ) && current_user_can( $this->cap, $user->ID ) ) {

			// Loop through new roles
			foreach ( $_POST['role'] as $blog_id => $new_role ) {

				// Switch to the blog
				if ( is_multisite() ) {
					switch_to_blog( $blog_id );
				}

				// Only allow switching to to editable role for site
				$editable_roles = get_editable_roles();
				if ( ! empty( $new_role ) && ! empty( $editable_roles[ $new_role ] ) ) {
					$user->set_role( $new_role );
				}

				// Switch back
				if ( is_multisite() ) {
					restore_current_blog();
				}
			}
		}

		// Allow third party plugins to save data in this section
		parent::save( $user );
	}
}
