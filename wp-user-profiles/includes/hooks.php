<?php

/**
 * User Profile Hooks
 *
 * @package Plugins/Users/Profiles/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Set constants early
add_action( 'init', 'wp_user_profiles_set_constants' );

// Initialize core profile sections
add_action( 'init', 'wp_user_profiles_register_profile_section'     );
add_action( 'init', 'wp_user_profiles_register_account_section'     );
add_action( 'init', 'wp_user_profiles_register_options_section'     );
add_action( 'init', 'wp_user_profiles_register_other_section'       );
add_action( 'init', 'wp_user_profiles_register_permissions_section' );
add_action( 'init', 'wp_user_profiles_register_sites_section'       );

// Initialize registered scripts
add_action( 'init', 'wp_user_profiles_admin_register_scripts' );

// Admin Menus
add_action( 'admin_menu',         'wp_user_profiles_admin_menus' );
add_action( 'network_admin_menu', 'wp_user_profiles_admin_menus' );
add_action( 'user_admin_menu',    'wp_user_profiles_admin_menus' );

// Admin Dependencies
add_action( 'wp_user_profiles_do_admin_head', 'wp_user_profiles_admin_enqueue_scripts' );
add_action( 'wp_user_profiles_do_admin_head', 'wp_user_profiles_admin_menu_highlight'  );
add_action( 'wp_user_profiles_do_admin_load', 'wp_user_profiles_add_meta_boxes'        );
add_action( 'wp_user_profiles_do_admin_load', 'wp_user_profiles_add_contextual_help'   );
add_action( 'wp_user_profiles_do_admin_load', 'wp_user_profiles_show_screen_options'   );

// Admin Meta Boxes
add_action( 'wp_user_profiles_add_meta_boxes', 'wp_user_profiles_add_status_meta_box', 10, 2 );

// Admin notices
add_action( 'wp_user_profiles_admin_notices', 'wp_user_profiles_admin_notices' );

// Admin Saving
add_action( 'admin_init',                                'wp_user_profiles_save_user'             );
add_filter( 'wp_user_profiles_save',                     'wp_user_profiles_save_user_status'      );
add_action( 'wp_user_profiles_get_admin_notices',        'wp_user_profiles_save_user_notices'     );
add_action( 'wp_user_profiles_save_permissions_section', 'wp_user_profiles_save_user_super_admin' );

// Capabilities
add_filter( 'map_meta_cap',     'wp_user_profiles_map_meta_cap', 10, 4 );

// Redirect
add_filter( 'load-profile.php',   'wp_user_profiles_old_profile_redirect'   );
add_filter( 'load-user-edit.php', 'wp_user_profiles_old_user_edit_redirect' );

// Links
add_filter( 'edit_profile_url',   'wp_user_profiles_edit_user_url_filter', 10, 3 );
add_filter( 'get_edit_user_link', 'wp_user_profiles_edit_user_url_filter', 10, 3 );

// Ajax Calls
add_action( 'wp_ajax_wp_user_profiles_common_roles',        'wp_user_profiles_get_common_user_roles_ajax' );
add_action( 'wp_ajax_wp_user_profiles_export_roles',        'wp_user_profiles_export_user_roles_ajax' );
add_action( 'wp_ajax_nopriv_wp_user_profiles_export_roles', 'wp_user_profiles_export_user_roles_ajax' );

// Back compat ("Other" section)
add_filter( 'wp_user_profiles_show_other_section', 'wp_user_profiles_has_profile_actions' );

// Nav & Subnav
add_action( 'wp_user_profiles_nav_actions', 'wp_user_profiles_admin_nav',    12 );
add_action( 'wp_user_profiles_nav_actions', 'wp_user_profiles_admin_subnav', 14 );

// BuddyPress
add_action( 'bp_init', 'wp_user_profiles_unhook_bp_profile_nav' );
