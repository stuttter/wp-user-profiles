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

	/**
	 * Add the meta boxes for this section
	 *
	 * @since 0.2.0
	 *
	 * @param  string  $type
	 * @param  WP_User $user
	 */
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

		// Language
		add_meta_box(
			'language',
			_x( 'Language', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_language_metabox',
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

		// Application Passwords
		if ( wp_user_profiles_user_supports( 'application-passwords', $user ) ) {
			add_meta_box(
				'application',
				_x( 'Application Passwords', 'users user-admin edit screen', 'wp-user-profiles' ),
				'wp_user_profiles_application_metabox',
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
	 * @return mixed Integer on success. WP_Error on failure.
	 */
	public function save( $user = null ) {

		// Password (1)
		$pass1 = isset( $_POST['pass1'] )
			? $_POST['pass1']
			: '';

		// Password (2)
		$pass2 = isset( $_POST['pass2'] )
			? $_POST['pass2']
			: '';

		// This filter is documented in wp-admin/includes/user.php
		do_action_ref_array( 'check_passwords', array( $user->user_login, &$pass1, &$pass2 ) );

		// Check for "\" in password
		if ( false !== strpos( wp_unslash( $pass1 ), "\\" ) ) {
			$this->errors->add( 'pass', __( '<strong>ERROR</strong>: Passwords may not contain the character "\\".', 'wp-user-profiles' ), array( 'form-field' => 'pass1' ) );
		}

		// Checking the password has been typed twice the same
		if ( $pass1 !== $pass2 ) {
			$this->errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter the same password in both password fields.', 'wp-user-profiles' ), array( 'form-field' => 'pass1' ) );
		}

		// Set the password
		if ( ! empty( $pass1 ) ) {
			$user->user_pass = $pass1;
		}

		// Checking locale
		if ( isset( $_POST['locale'] ) ) {
			$locale = sanitize_text_field( $_POST['locale'] );

			if ( 'site-default' === $locale ) {
				$locale = '';
			} elseif ( '' === $locale ) {
				$locale = 'en_US';
			} elseif ( ! in_array( $locale, get_available_languages(), true ) ) {
				$locale = '';
			}

			$user->locale = $locale;
		}

		// Checking email address
		if ( isset( $_POST['email'] ) ) {

			// Sanitize the email
			$user->user_email = sanitize_text_field( wp_unslash( $_POST['email'] ) );

			// Email empty
			if ( empty( $user->user_email ) ) {
				$this->errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please enter an email address.', 'wp-user-profiles' ), array( 'form-field' => 'email' ) );

			// Email invalid
			} elseif ( ! is_email( $user->user_email ) ) {
				$this->errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address is not correct.', 'wp-user-profiles' ), array( 'form-field' => 'email' ) );

			// Email in use
			} elseif ( ( $owner_id = email_exists( $user->user_email ) ) && ( $owner_id !== $user->ID ) ) {
				$this->errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already in use.', 'wp-user-profiles' ), array( 'form-field' => 'email' ) );
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
				'<p>'  . esc_html__( 'This is where important account information can be found.',                'wp-user-profiles' ) . '</p><ul>' .
				'<li>' . esc_html__( 'Your email address is used for receiving notifications from this site',    'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'Passwords should be lengthy and complex to help keep your account secure', 'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'The language you pick will be used wherever it is supported.',             'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'Sessions are logged from each device you login from',                      'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'Application passwords allow authentication via non-interactive systems.',  'wp-user-profiles' ) . '</li></ul>'
		) );
	}
}
