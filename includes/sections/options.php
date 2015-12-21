<?php

/**
 * User Profile Options Section
 *
 * @package Plugins/Users/Profiles/Sections/Options
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * User Profiles "Options" class
 *
 * @since 0.2.0
 */
class WP_User_Profile_Options_Section extends WP_User_Profile_Section {

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

		// Color schemes (only if available)
		if ( count( $GLOBALS['_wp_admin_css_colors'] ) && has_action( 'admin_color_scheme_picker' ) ) {
			add_meta_box(
				'colors',
				_x( 'Color Scheme', 'users user-admin edit screen', 'wp-user-profiles' ),
				'wp_user_profiles_color_scheme_metabox',
				$type,
				'normal',
				'high',
				$user
			);
		}

		// Color schemes
		add_meta_box(
			'options',
			_x( 'Personal Options', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_personal_options_metabox',
			$type,
			'normal',
			'core',
			$user
		);
	}
}
