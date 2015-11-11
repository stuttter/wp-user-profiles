<?php

/**
 * User Profile Hooks
 * 
 * @package Plugins/Users/Profiles/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Admin Menus
add_action( 'admin_menu',         'wp_user_profiles_admin_menus' );
add_action( 'network_admin_menu', 'wp_user_profiles_admin_menus' );
add_action( 'user_admin_menu',    'wp_user_profiles_admin_menus' );

add_action( 'admin_enqueue_scripts', 'wp_user_profiles_admin_enqueue_scripts' );

// Metaboxes
add_action( 'add_meta_boxes', 'wp_user_profiles_add_status_metabox',         10, 2 );
add_action( 'add_meta_boxes', 'wp_user_profiles_add_profile_meta_boxes',     10, 2 );
add_action( 'add_meta_boxes', 'wp_user_profiles_add_account_meta_boxes',     10, 2 );
add_action( 'add_meta_boxes', 'wp_user_profiles_add_options_meta_boxes',     10, 2 );
add_action( 'add_meta_boxes', 'wp_user_profiles_add_permissions_meta_boxes', 10, 2 );

// Profiles
add_filter( 'edit_profile_url',   'wp_user_profiles_edit_user_url_filter', 10, 3 );
add_filter( 'get_edit_user_link', 'wp_user_profiles_edit_user_url_filter', 10, 3 );

// Admin notices
add_action( 'wp_user_profiles_admin_notices', 'wp_user_profiles_admin_notices' );

// Save the user
add_action( 'admin_init',                         'wp_user_profiles_save_user'         );
add_filter( 'wp_user_profiles_get_admin_notices', 'wp_user_profiles_save_user_notices' );
