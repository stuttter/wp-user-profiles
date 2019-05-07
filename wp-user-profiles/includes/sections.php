<?php

/**
 * User Profile Sections
 *
 * @package Plugins/Users/Profiles/Sections
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register the "Profile" section
 *
 * @since 0.2.0
 *
 * @return WP_User_Profile_Section
 */
function wp_user_profiles_register_profile_section() {
	new WP_User_Profile_Profile_Section( array(
		'id'    => 'profile',
		'slug'  => 'profile',
		'name'  => esc_html__( 'Profile', 'wp-user-profiles' ),
		'cap'   => 'edit_profile',
		'icon'  => 'dashicons-admin-users',
		'order' => 70
	) );
}

/**
 * Register the "Account" section
 *
 * @since 0.2.0
 *
 * @return WP_User_Profile_Section
 */
function wp_user_profiles_register_account_section() {
	new WP_User_Profile_Account_Section( array(
		'id'    => 'account',
		'slug'  => 'account',
		'name'  => esc_html__( 'Account', 'wp-user-profiles' ),
		'cap'   => 'edit_profile',
		'icon'  => 'dashicons-admin-generic',
		'order' => 75
	) );
}

/**
 * Register the "Options" section
 *
 * @since 0.2.0
 *
 * @return WP_User_Profile_Section
 */
function wp_user_profiles_register_options_section() {
	new WP_User_Profile_Options_Section( array(
		'id'    => 'options',
		'slug'  => 'options',
		'name'  => esc_html__( 'Options', 'wp-user-profiles' ),
		'cap'   => 'edit_profile',
		'icon'  => 'dashicons-admin-settings',
		'order' => 80
	) );
}

/**
 * Register the "Other" section
 *
 * @since 0.2.0
 *
 * @return WP_User_Profile_Section
 */
function wp_user_profiles_register_other_section() {

	// Bail if no profile actions are registered
	if ( ! apply_filters( 'wp_user_profiles_show_other_section', false ) ) {
		return;
	}

	// Actually register the section
	new WP_User_Profile_Other_Section( array(
		'id'    => 'other',
		'slug'  => 'other',
		'name'  => esc_html__( 'Other', 'wp-user-profiles' ),
		'cap'   => 'edit_profile',
		'icon'  => 'dashicons-admin-generic',
		'order' => 85
	) );
}

/**
 * Register the "Options" section
 *
 * @since 0.2.0
 *
 * @return WP_User_Profile_Section
 */
function wp_user_profiles_register_permissions_section() {
	new WP_User_Profile_Permissions_Section( array(
		'id'    => 'permissions',
		'slug'  => 'permissions',
		'name'  => esc_html__( 'Permissions', 'wp-user-profiles' ),
		'cap'   => 'edit_profile',
		'icon'  => 'dashicons-hidden',
		'order' => 85
	) );
}

/**
 * Register the "Sites" section
 *
 * @since 1.0.0
 *
 * @return WP_User_Profile_Section
 */
function wp_user_profiles_register_sites_section() {
	if ( is_multisite() ) {
		new WP_User_Profile_Sites_Section( array(
			'id'    => 'sites',
			'slug'  => 'sites',
			'name'  => esc_html__( 'Sites', 'wp-user-profiles' ),
			'cap'   => 'edit_profile',
			'icon'  => 'dashicons-admin-multisite',
			'order' => 90
		) );
	}
}
