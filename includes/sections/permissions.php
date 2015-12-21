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
}
