<?php

/**
 * User Profile Profile Section
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

	/**
	 * Save section data
	 *
	 * @since 0.2.0
	 *
	 * @param WP_User $user
	 */
	public function save( $user = null ) {

		// Setup the error handler
		$errors = new WP_Error();

		// User Login
		if ( isset( $_POST['user_login'] ) ) {

			// Invalid login
			if ( ! validate_username( $_POST['user_login'] ) ) {
				$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
			}

			// Login already exists
			if ( username_exists( $_POST['user_login'] ) ) {
				$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.' ) );
			}

			// Checking that username has been typed
			if ( empty( $user->user_login ) ) {
				$errors->add( 'user_login', __( '<strong>ERROR</strong>: Please enter a username.' ) );
			}

			// No errors
			if ( ! $errors->get_error_code() ) {
				$user->user_login = sanitize_user( $_POST['user_login'], true );
			} else {
				return $errors;
			}
		}

		// First
		$user->first_name = isset( $_POST['first_name'] )
			? sanitize_text_field( $_POST['first_name'] )
			: '';

		// Last
		$user->last_name = isset( $_POST['last_name'] )
			? sanitize_text_field( $_POST['last_name'] )
			: '';

		// Nickname
		if ( isset( $_POST['nickname'] ) ) {
			if ( empty( $_POST['nickname'] ) ) {
				$errors->add( 'nickname', __( '<strong>ERROR</strong>: Please enter a nickname.' ) );
				return $errors;
			}

			// Set the nick
			$user->nickname = sanitize_text_field( $_POST['nickname'] );
		}

		// Display
		$user->display_name = isset( $_POST['display_name'] )
			? sanitize_text_field( $_POST['display_name'] )
			: '';

		// Description
		$user->description = isset( $_POST['description'] )
			? trim( $_POST['description'] )
			: '';

		// Website
		if ( isset( $_POST['url'] ) ) {

			// Emptying URL
			if ( empty( $_POST['url'] ) || in_array( $_POST['url'], wp_allowed_protocols(), true ) ) {
				$user->user_url = '';

			// Validate
			} else {
				$user->user_url = esc_url_raw( $_POST['url'] );
				$protocols      = implode( '|', array_map( 'preg_quote', wp_allowed_protocols() ) );
				$user->user_url = preg_match( '/^(' . $protocols . '):/is', $user->user_url )
					? $user->user_url
					: 'http://' . $user->user_url;
			}
		}

		// Allow third party plugins to save data in this section
		parent::save( $user );
	}
}
