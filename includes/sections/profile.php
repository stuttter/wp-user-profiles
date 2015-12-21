<?php

/**
 * User Profile Base Section
 *
 * @package Plugins/Users/Profiles/Sections/Profile
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * User Profiles "Profile" class
 *
 * @since 0.2.0
 */
class WP_User_Profile_Profile_Section extends WP_User_Profile_Section {

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

		// Name
		add_meta_box(
			'name',
			_x( 'Name', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_name_metabox',
			$type,
			'normal',
			'high',
			$user
		);

		// About
		add_meta_box(
			'about',
			_x( 'About', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_about_metabox',
			$type,
			'normal',
			'core',
			$user
		);

		// Contact, if methods are registered
		if ( wp_get_user_contact_methods( $user ) ) {
			add_meta_box(
				'contact',
				_x( 'Contact', 'users user-admin edit screen', 'wp-user-profiles' ),
				'wp_user_profiles_contact_metabox',
				$type,
				'normal',
				'low',
				$user
			);
		}
	}
}
