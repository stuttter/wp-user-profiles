<?php

/**
 * Add the default user profile metaboxes
 *
 * @since 0.1.0
 *
 * @param   string  $type
 * @param   mixed   $user
 */
function wp_user_profiles_add_profile_meta_boxes( $type = '', $user = null ) {

	// Bail if not user metaboxes
	if ( ( 'admin_page_profile' !== $type ) || empty( $user ) ) {
		return;
	}

	// Register metaboxes for the user edit screen
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_status_metabox',
		$type,
		'side',
		'core'
	);

	// Name
	add_meta_box(
		'name',
		_x( 'Name', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_name_metabox',
		$type,
		'normal',
		'core'
	);

	// About
	add_meta_box(
		'about',
		_x( 'About', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_about_metabox',
		$type,
		'normal',
		'core'
	);

	// Contact, if methods are registered
	if ( wp_get_user_contact_methods( $user ) ) {
		add_meta_box(
			'contact',
			_x( 'Contact', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_contact_metabox',
			$type,
			'normal',
			'core'
		);
	}
}

/**
 * Add the default user profile metaboxes
 *
 * @since 0.1.0
 *
 * @param   string  $type
 * @param   mixed   $user
 */
function wp_user_profiles_add_account_meta_boxes( $type = '', $user = null ) {

	// Bail if not user metaboxes
	if ( ( 'admin_page_account' !== $type ) || empty( $user ) ) {
		return;
	}

	// Status
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_status_metabox',
		$type,
		'side',
		'core'
	);

	// Email
	add_meta_box(
		'email',
		_x( 'Email', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_email_metabox',
		$type,
		'normal',
		'core'
	);

	// Password
	add_meta_box(
		'password',
		_x( 'Password', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_password_metabox',
		$type,
		'normal',
		'core'
	);

	// Sessions
	add_meta_box(
		'sessions',
		_x( 'Sessions', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_session_metabox',
		$type,
		'normal',
		'core'
	);
}

/**
 * Add the default user profile metaboxes
 *
 * @since 0.1.0
 *
 * @param   string  $type
 * @param   mixed   $user
 */
function wp_user_profiles_add_options_meta_boxes( $type = '', $user = null ) {

	// Bail if not user metaboxes
	if ( ( 'admin_page_options' !== $type ) || empty( $user ) ) {
		return;
	}

	// Always register the status box
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_status_metabox',
		$type,
		'side',
		'core'
	);

	// Color schemes
	add_meta_box(
		'colors',
		_x( 'Color Scheme', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_color_scheme_metabox',
		$type,
		'normal',
		'core'
	);

	// Color schemes
	add_meta_box(
		'options',
		_x( 'Personal Options', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_personal_options_metabox',
		$type,
		'normal',
		'core'
	);
}

/**
 * Add the default user profile metaboxes
 *
 * @since 0.1.0
 *
 * @param   string  $type
 * @param   mixed   $user
 */
function wp_user_profiles_add_permissions_meta_boxes( $type = '', $user = null ) {

	// Bail if not user metaboxes
	if ( ( 'admin_page_permissions' !== $type ) || empty( $user ) ) {
		return;
	}

	// Always register the status box
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_status_metabox',
		$type,
		'side',
		'core'
	);

	// Color schemes
	add_meta_box(
		'roles',
		_x( 'Roles', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_roles_metabox',
		$type,
		'normal',
		'core'
	);

	// Color schemes
	add_meta_box(
		'options',
		_x( 'Additional Capabilities', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_additional_capabilities_metabox',
		$type,
		'normal',
		'core'
	);
}
