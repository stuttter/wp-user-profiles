<?php

/**
 * User Profile Dependencies
 *
 * @package Plugins/Users/Profiles/Dependencies
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Subaction for doing the admin head action
 *
 * @since 0.2.0
 *
 * @param string $hook
 */
function wp_user_profiles_do_admin_head( $hook = '' ) {
	do_action( 'wp_user_profiles_do_admin_head', $hook );
}

/**
 * Subaction for doing the admin load action
 *
 * @since 0.2.0
 *
 * @param string $hook
 */
function wp_user_profiles_do_admin_load( $hook = '' ) {
	do_action( 'wp_user_profiles_do_admin_load', $hook );
}
