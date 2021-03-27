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
	 * @param array  $args
	 */
	public function add_meta_boxes( $type = '', $args = array() ) {

		// Allow third party plugins to add metaboxes
		parent::add_meta_boxes( $type, $args );

		// Name
		add_meta_box(
			'name',
			_x( 'Name', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_name_metabox',
			$type,
			'normal',
			'high',
			$args
		);

		// About
		add_meta_box(
			'about',
			_x( 'About', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_about_metabox',
			$type,
			'normal',
			'core',
			$args
		);

		// Contact, if methods are registered
		if ( wp_get_user_contact_methods( $args['user'] ) ) {
			add_meta_box(
				'contact',
				_x( 'Contact', 'users user-admin edit screen', 'wp-user-profiles' ),
				'wp_user_profiles_contact_metabox',
				$type,
				'normal',
				'low',
				$args
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

		// User Login
		if ( isset( $_POST['user_login'] ) ) {

			// Set the login
			$user->user_login = sanitize_user( $_POST['user_login'], true );

			// Invalid login
			if ( ! validate_username( $user->user_login ) ) {
				$this->errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'wp-user-profiles' ) );
			}

			// Login already exists
			if ( username_exists( $user->user_login ) ) {
				$this->errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.', 'wp-user-profiles' ) );
			}

			// Checking that username has been typed
			if ( empty( $user->user_login ) ) {
				$this->errors->add( 'user_login', __( '<strong>ERROR</strong>: Please enter a username.', 'wp-user-profiles' ) );
			}

			// Return if errored
			if ( $this->errors->get_error_code() ) {
				return $this->errors;
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

			// Set the nick
			$user->nickname = sanitize_text_field( $_POST['nickname'] );

			// Nickname was empty
			if ( empty( $user->nickname ) ) {
				$this->errors->add( 'nickname', __( '<strong>ERROR</strong>: Please enter a nickname.', 'wp-user-profiles' ) );
				return $this->errors;
			}
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

		// Look for contact methods
		$methods = wp_get_user_contact_methods( $user );

		// Contact methods
		foreach ( array_keys( $methods ) as $method ) {
			if ( isset( $_POST[ $method ] ) ) {
				$user->{$method} = sanitize_text_field( $_POST[ $method ] );
			}
		}

		// Allow third party plugins to save data in this section
		parent::save( $user );
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
				'<p>'  . esc_html__( 'This is where most biographical information can be found.', 'wp-user-profiles' ) . '</p><ul>' .
				'<li>' . esc_html__( 'First and Last Name',                                       'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'Nickname and Display Name',                                 'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'Website and Biography',                                     'wp-user-profiles' ) . '</li></ul>'
		) );
	}
}
