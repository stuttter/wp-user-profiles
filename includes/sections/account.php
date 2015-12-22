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
	 * @param string $type
	 * @param object $user
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

	/**
	 * Save section data
	 *
	 * @since 0.2.0
	 *
	 * @param WP_User $user
	 */
	public function save( $user = null ) {

		// Password changes
		$pass1 = isset( $_POST['pass1'] )
			? $_POST['pass1']
			: '';

		$pass2 = isset( $_POST['pass2'] )
			? $_POST['pass2']
			: '';

		/**
		 * Fires before the password and confirm password fields are checked for congruity.
		 *
		 * @since 1.5.1
		 *
		 * @param string $user_login The username.
		 * @param string &$pass1     The password, passed by reference.
		 * @param string &$pass2     The confirmed password, passed by reference.
		 */
		do_action_ref_array( 'check_passwords', array( $user->user_login, &$pass1, &$pass2 ) );

		// Check for "\" in password
		if ( false !== strpos( wp_unslash( $pass1 ), "\\" ) ) {
			$this->errors->add( 'pass', __( '<strong>ERROR</strong>: Passwords may not contain the character "\\".' ), array( 'form-field' => 'pass1' ) );
		}

		// Checking the password has been typed twice the same
		if ( $pass1 !== $pass2 ) {
			$this->errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter the same password in both password fields.' ), array( 'form-field' => 'pass1' ) );
		}

		// Set the password
		if ( ! empty( $pass1 ) ) {
			$user->user_pass = $pass1;
		}

		// Checking email address
		if ( isset( $_POST['email'] ) ) {

			// Sanitize the email
			$user->user_email = sanitize_text_field( wp_unslash( $_POST['email'] ) );

			// Email empty
			if ( empty( $user->user_email ) ) {
				$this->errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please enter an email address.' ), array( 'form-field' => 'email' ) );

			// Email invalid
			} elseif ( ! is_email( $user->user_email ) ) {
				$this->errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address is not correct.' ), array( 'form-field' => 'email' ) );

			// Email in use
			} elseif ( ( $owner_id = email_exists( $user->user_email ) ) && ( $owner_id !== $user->ID ) ) {
				$this->errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already in use.' ), array( 'form-field' => 'email' ) );
			}
		}

		// Bail if password change errors occurred
		if ( $this->errors->get_error_code() ) {
			return $this->errors;
		}

		// Allow third party plugins to save data in this section
		parent::save( $user );
	}
}
