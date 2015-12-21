<?php

/**
 * User Profile Account Section
 *
 * @package Plugins/Users/Profiles/Sections/Account
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * User Profiles "Account" class
 *
 * @since 0.2.0
 */
class WP_User_Profile_Account_Section extends WP_User_Profile_Section {
	
	public function add_meta_boxes( $type = '', $user = null ) {

		// Allow third party plugins to add metaboxes
		parent::add_meta_boxes( $type, $user );

		// Email
		add_meta_box(
			'email',
			_x( 'Email', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_email_metabox',
			$type,
			'normal',
			'high',
			$user
		);

		// Password
		add_meta_box(
			'password',
			_x( 'Password', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_password_metabox',
			$type,
			'normal',
			'core',
			$user
		);

		// Sessions
		add_meta_box(
			'sessions',
			_x( 'Sessions', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_session_metabox',
			$type,
			'normal',
			'low',
			$user
		);
	}
}
