<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the file all menus will use as their parent
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_user_profiles_get_file() {
	return apply_filters( 'wp_user_profiles_get_file', 'users.php' );
}

/**
 * Conditionally filter the URL used to edit a user
 *
 * This function does some primitive routing for theme-side user profile editing,
 * but since this is largely in flux and likely related to several other plugins,
 * themes, and other factors, we'll be tweaking this a bit in the future.
 *
 * @since 0.1.0
 *
 * @param  string  $url
 * @param  int     $user_id
 * @param  string  $scheme
 *
 * @return string
 */
function wp_user_profiles_edit_user_url_filter( $url = '', $user_id = 0, $scheme = '' ) {

	// Admin area editing
	if ( is_admin() ) {
		$url = wp_user_profiles_get_admin_area_url( $user_id, $scheme );

	// Theme side editing
	} else {
		$url = wp_user_profiles_get_edit_user_url( $user_id );
	}

	return add_query_arg( array( 'page' => 'profile' ), $url );
}

/**
 * Return an array of profile sections
 *
 * @since 0.1.0
 *
 * @param   array  $args
 *
 * @return  array
 */
function wp_user_profiles_sections( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(

		// Profile
		'profile' => array(
			'slug' => 'profile',
			'name' => esc_html__( 'Profile', 'wp-user-profiles' ),
			'cap'  => 'edit_user'
		),

		// Acount
		'account' => array(
			'slug' => 'account',
			'name' => esc_html__( 'Account', 'wp-user-profiles' ),
			'cap'  => 'edit_user'
		),

		// Options
		'options' => array(
			'slug' => 'options',
			'name' => esc_html__( 'Options', 'wp-user-profiles' ),
			'cap'  => 'edit_user'
		),

		// Permissions
		'permissions' => array(
			'slug' => 'permissions',
			'name' => esc_html__( 'Permissions', 'wp-user-profiles' ),
			'cap'  => 'edit_user'
		)
	) );

	// Filter & return
	return apply_filters( 'wp_user_profiles_sections', $r, $args );
}

/**
 * Return the admin area URL for a user
 *
 * @since 0.1.0
 *
 * @param  int     $user_id
 * @param  string  $scheme
 * @param  array   $args
 *
 * @return string
 */
function wp_user_profiles_get_admin_area_url( $user_id = 0, $scheme = '', $args = array() ) {

	$file = wp_user_profiles_get_file();

	// User admin (multisite only)
	if ( is_user_admin() ) {
		$url = user_admin_url( $file, $scheme );

	// Network admin editing
	} elseif ( is_network_admin() ) {
		$url = network_admin_url( $file, $scheme );

	// Fallback dashboard
	} else {
		$url = get_dashboard_url( $user_id, $file, $scheme );
	}

	// Add user ID to args array for other users
	if ( ! empty( $user_id ) && ( $user_id !== get_current_user_id() ) ) {
		$args['user_id'] = $user_id;
	}

	// Add query args
	$url = add_query_arg( $args, $url );

	// Filter and return
	return apply_filters( 'wp_user_profiles_get_admin_area_url', $url, $user_id, $scheme, $args );
}

function wp_user_profiles_get_edit_user_url( $user_id = 0 ) {
	return '';
}

/**
 * Edit user settings based on contents of $_POST
 *
 * Largely based on the edit_user() function, this function only throws errors
 * when the user has posted invalid data, vs. when the mock user object does not
 * contain it.
 *
 * @since 0.1.0
 *
 * @param int $user_id Optional. User ID.
 * @return int|WP_Error user id of the updated user
 */
function wp_user_profiles_edit_user( $user_id = 0 ) {

	// Bail if no user ID
	if ( empty( $user_id ) ) {
		return;
	}

	// Setup the user being saved
	$user     = new stdClass;
	$user->ID = (int) $user_id;
	$userdata = get_userdata( $user_id );

	// Setup the user login
	if ( isset( $_POST['user_login'] ) ) {
		$user->user_login = sanitize_user( $_POST['user_login'], true );
	} else {
		$user->user_login = wp_slash( $userdata->user_login );
	}

	// Password changes
	$pass1 = isset( $_POST['pass1'] )
		? $_POST['pass1']
		: '';

	$pass2 = isset( $_POST['pass2'] )
		? $_POST['pass2']
		: '';

	// Role changes
	if ( isset( $_POST['role'] ) && current_user_can( 'edit_users' ) ) {

		// New roles
		$new_roles = array_map( 'sanitize_text_field', array_values( $_POST['role'] ) );
		$wp_roles  = wp_roles();

		// Loop through new roles
		foreach ( $new_roles as $new_role ) {
			$potential_role = isset( $wp_roles->role_objects[ $new_role ] )
				? $wp_roles->role_objects[ $new_role ]
				: false;

			// Don't let anyone with 'edit_users' (admins) edit their own role to something without it.
			// Multisite super admins can freely edit their blog roles -- they possess all caps.
			if ( ( is_multisite() && current_user_can( 'manage_sites' ) ) || $user_id != get_current_user_id() || ($potential_role && $potential_role->has_cap( 'edit_users' ) ) ) {
				$user->role = $new_role;
			}

			// If the new role isn't editable by the logged-in user die with error
			$editable_roles = get_editable_roles();
			if ( ! empty( $new_role ) && empty( $editable_roles[ $new_role ] ) ) {
				wp_die( __( 'You can&#8217;t give users that role.' ) );
			}
		}
	}

	// Email
	if ( isset( $_POST['email'] ) ) {
		$user->user_email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
	}

	// Website
	if ( isset( $_POST['url'] ) ) {
		if ( empty ( $_POST['url'] ) || $_POST['url'] == 'http://' ) {
			$user->user_url = '';
		} else {
			$user->user_url = esc_url_raw( $_POST['url'] );
			$protocols = implode( '|', array_map( 'preg_quote', wp_allowed_protocols() ) );
			$user->user_url = preg_match('/^(' . $protocols . '):/is', $user->user_url) ? $user->user_url : 'http://'.$user->user_url;
		}
	}

	// First
	if ( isset( $_POST['first_name'] ) ) {
		$user->first_name = sanitize_text_field( $_POST['first_name'] );
	}

	// Last
	if ( isset( $_POST['last_name'] ) ) {
		$user->last_name = sanitize_text_field( $_POST['last_name'] );
	}

	// Nick
	if ( isset( $_POST['nickname'] ) ) {
		$user->nickname = sanitize_text_field( $_POST['nickname'] );
	}

	// Display
	if ( isset( $_POST['display_name'] ) ) {
		$user->display_name = sanitize_text_field( $_POST['display_name'] );
	}

	// Description
	if ( isset( $_POST['description'] ) ) {
		$user->description = trim( $_POST['description'] );
	}

	// Contact methods
	foreach ( wp_get_user_contact_methods( $user ) as $method => $name ) {
		if ( isset( $_POST[ $method ] )) {
			$user->$method = sanitize_text_field( $_POST[ $method ] );
		}
	}

	// Options
	$user->rich_editing = isset( $_POST['rich_editing'] ) && ( 'false' === $_POST['rich_editing'] )
		? 'false'
		: 'true';

	$user->admin_color = isset( $_POST['admin_color'] )
		? sanitize_text_field( $_POST['admin_color'] )
		: 'fresh';

	$user->show_admin_bar_front = isset( $_POST['admin_bar_front'] )
		? 'true'
		: 'false';

	$user->comment_shortcuts = isset( $_POST['comment_shortcuts'] ) && ( 'true' === $_POST['comment_shortcuts'] )
		? 'true'
		: '';

	$user->use_ssl = 0;
	if ( ! empty( $_POST['use_ssl'] ) ) {
		$user->use_ssl = 1;
	}

	// Error checking
	$errors = new WP_Error();

	// Checking that username has been typed
	if ( isset( $_POST['user_login'] ) && empty( $user->user_login ) ) {
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: Please enter a username.' ) );
	}

	// Checking that nickname has been typed
	if ( isset( $_POST['nickname'] ) && empty( $user->nickname ) ) {
		$errors->add( 'nickname', __( '<strong>ERROR</strong>: Please enter a nickname.' ) );
	}

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
		$errors->add( 'pass', __( '<strong>ERROR</strong>: Passwords may not contain the character "\\".' ), array( 'form-field' => 'pass1' ) );
	}

	// Checking the password has been typed twice the same
	if ( $pass1 !== $pass2 ) {
		$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter the same password in both password fields.' ), array( 'form-field' => 'pass1' ) );
	}

	if ( ! empty( $pass1 ) ) {
		$user->user_pass = $pass1;
	}

	if ( isset( $_POST['user_login'] ) && ! validate_username( $_POST['user_login'] ) ) {
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
	}

	if ( isset( $_POST['user_login'] ) && username_exists( $user->user_login ) ) {
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.' ) );
	}

	// Checking email address
	if ( isset( $_POST['email'] ) ) {
		if ( empty( $user->user_email ) ) {
			$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please enter an email address.' ), array( 'form-field' => 'email' ) );
		} elseif ( ! is_email( $user->user_email ) ) {
			$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address is not correct.' ), array( 'form-field' => 'email' ) );
		} elseif ( ( $owner_id = email_exists( $user->user_email ) ) && ( $owner_id !== $user->ID ) ) {
			$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already in use.' ), array( 'form-field' => 'email' ) );
		}
	}

	/**
	 * Fires before user profile update errors are returned.
	 *
	 * @since 2.8.0
	 *
	 * @param WP_Error &$errors WP_Error object, passed by reference.
	 * @param bool     $update  Whether this is a user update.
	 * @param WP_User  &$user   WP_User object, passed by reference.
	 */
	do_action_ref_array( 'user_profile_update_errors', array( &$errors, true, &$user ) );

	// Return errors if there are any
	if ( $errors->get_error_codes() ) {
		return $errors;
	}

	return wp_update_user( $user );
}

/**
 * Save the user when they click "Updat"
 *
 * @since 0.1.0
 */
function wp_user_profiles_save_user() {

	// Bail if not updating a user
	if ( empty( $_POST['user_id'] ) || empty( $_POST['action'] ) ) {
		return;
	}

	// Bail if not updating a user
	if ( 'update' !== $_POST['action'] ) {
		return;
	}

	// Set the user ID
	$user_id = (int) $_POST['user_id'];

	// Referring?
	if ( ! empty( $_REQUEST['wp_http_referer'] ) ) {
		$wp_http_referer = $_REQUEST['wp_http_referer'];
	} else {
		$wp_http_referer = false;
	}

	// Update the user
	$errors = wp_user_profiles_edit_user( $user_id );

	// Grant or revoke super admin status if requested.
	if ( is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) && ! isset( $super_admins ) && empty( $_POST['super_admin'] ) == is_super_admin( $user_id ) ) {
		empty( $_POST['super_admin'] )
			? revoke_super_admin( $user_id )
			: grant_super_admin( $user_id );
	}

	// No errors
	if ( ! is_wp_error( $errors ) ) {
		$redirect = add_query_arg( 'updated', true );

		if ( ! empty( $wp_http_referer ) ) {
			$redirect = add_query_arg( 'wp_http_referer', urlencode( $wp_http_referer ), $redirect );
		}

		wp_redirect( $redirect );

		exit;

	// Errors
	} else {
		wp_die( $errors );
	}
}

/**
 * Add a notice when a profile is updated
 *
 * @since 0.1.0
 *
 * @param  mixed $notice
 * @return array
 */
function wp_user_profiles_save_user_notices( $notice = array() ) {

	// Bail
	if ( empty( $_GET['action'] ) || ( 'update' !== $_GET['action'] ) ) {
		return;
	}

	return array(
		'message' => esc_html__( 'User updated.', 'wp-user-profiles' ),
		'class'   => 'updated'
	);
}
