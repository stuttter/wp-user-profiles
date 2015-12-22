<?php

/**
 * User Profile Screen Options
 *
 * @package Plugins/Users/Profiles/ScreenOptions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;


/**
 * Show "Screen Options" when viewing profile pages
 *
 * @since 0.2.0
 */
function wp_user_profiles_show_screen_options() {
	add_filter( 'screen_options_show_screen', '__return_true' );
}
