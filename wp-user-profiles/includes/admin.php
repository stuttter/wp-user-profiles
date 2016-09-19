<?php

/**
 * User Profile Admin
 *
 * @package Plugins/Users/Profiles/Admin
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Enqueue admin scripts
 *
 * @since 0.1.0
 */
function wp_user_profiles_admin_enqueue_scripts() {

	// Enqueue core scripts
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'dashboard' );

	// Set location & version for scripts & styles
	$src = wp_user_profiles_get_plugin_url();
	$ver = wp_user_profiles_get_asset_version();

	// Styles
	wp_enqueue_style( 'wp-user-profiles', $src . 'assets/css/user-profiles.css', array(), $ver );

	// Ugh... this is terrible
	wp_enqueue_script( 'user-profile', $src . 'assets/css/user-profiles.css', array( 'jquery', 'dashboard', 'password-strength-meter', 'wp-util' ), $ver );
	wp_scripts()->registered['user-profile']->src = $src . 'assets/js/user-profiles.js';
}

/**
 * Add admin pages and setup submenus
 *
 * @since 0.1.0
 */
function wp_user_profiles_admin_menus() {

	// Empty hooks array
	$file     = wp_user_profiles_get_file();
	$sections = wp_user_profiles_sections();

	// Add a visbile "Your Profile" link
	if ( 'users.php' === $file ) {

		// Remove the core "Your Profile" submenu
		unset( $GLOBALS['submenu']['users.php'][15] );

		// Add (and quickly remove) submenu pages
		foreach ( $sections as $tab ) {
			$hook = add_submenu_page( $file, $tab->name, $tab->name, $tab->cap, $tab->slug, 'wp_user_profiles_user_admin' );
			add_action( "admin_head-{$hook}", 'wp_user_profiles_do_admin_head', $hook );
			add_action( "load-{$hook}",       'wp_user_profiles_do_admin_load', $hook );
			remove_submenu_page( $file, $tab->slug );
		}

		// Re-add new "Your Profile" submenu
		add_submenu_page( $file, esc_html__( 'Your Profile', 'wp-user-profiles' ), esc_html__( 'Your Profile', 'wp-user-profiles' ), 'read', 'profile', 'wp_user_profiles_user_admin' );

	// User admin needs some coercing
	} elseif ( is_user_admin() ) {
		remove_menu_page( 'profile.php' );

		foreach ( $sections as $tab ) {
			$hook = add_menu_page( $tab->name, $tab->name, $tab->cap, $tab->slug, 'wp_user_profiles_user_admin', $tab->icon, $tab->order );
			add_action( "admin_head-{$hook}", 'wp_user_profiles_do_admin_head', $hook );
			add_action( "load-{$hook}",       'wp_user_profiles_do_admin_load', $hook );
		}

	// Blog admin, but "Profile Mode" is en effect
	} else {
		remove_menu_page( 'profile.php' );

		add_menu_page( esc_html__( 'Profile', 'wp-user-profiles' ), esc_html__( 'Profile', 'wp-user-profiles' ), 'read', 'profile', 'wp_user_profiles_user_admin', 'dashicons-admin-users', 5 );

		foreach ( $sections as $tab ) {
			$hook = add_submenu_page( $file, $tab->name, $tab->name, $tab->cap, $tab->slug, 'wp_user_profiles_user_admin' );
			add_action( "admin_head-{$hook}", 'wp_user_profiles_do_admin_head', $hook );
			add_action( "load-{$hook}",       'wp_user_profiles_do_admin_load', $hook );
		}
	}
}

/**
 * Fix submenu highlights
 *
 * @since 0.1.0
 *
 * @global  string  $plugin_page
 * @global  string  $submenu_file
 */
function wp_user_profiles_admin_menu_highlight() {
	global $plugin_page, $submenu_file;

	// Bail if in user dashboard area
	if ( is_user_admin() ) {
		return;
	}

	// If not current user's profile page, set to Users and bail
	if ( ! empty( $_GET['user_id'] ) && ( get_current_user_id() !== (int) $_GET['user_id'] ) ) {
		$submenu_file = wp_user_profiles_get_file();
		return;
	}

	// Get slugs from profile sections
	$plucked = wp_user_profiles_get_section_hooknames();

	// Maybe tweak the highlighted submenu
	if ( ! in_array( $plugin_page, array( $plucked ), true ) ) {
		if ( current_user_can( 'list_users' ) ) {
			$submenu_file = 'profile';
		} elseif ( is_blog_admin() ) {
			$plugin_page  = 'profile';
		}
	}
}

/**
 * User profile admin notices
 *
 * @since 0.1.0
 */
function wp_user_profiles_admin_notices() {

	// No referrer
	$wp_http_referer = false;
	if ( ! empty( $_REQUEST['wp_http_referer'] ) ) {
		$wp_http_referer = remove_query_arg( array( 'action', 'updated' ), $_REQUEST['wp_http_referer'] );
	}

	// Get notices, if any
	$notice = apply_filters( 'wp_user_profiles_get_admin_notices', false, $wp_http_referer );

	// Bail if no notice
	if ( empty( $notice ) ) {
		return;
	}

	// Check for conditional classes
	$updated     = isset( $notice['classes'] ) && in_array( 'updated',        $notice['classes'], true );
	$dismissible = isset( $notice['classes'] ) && in_array( 'is-dismissible', $notice['classes'], true ); ?>

	<div id="message" class="<?php echo esc_attr( implode( ' ', $notice['classes'] ) ); ?>">

		<p><?php echo esc_html( $notice['message'] ); ?></p>

		<?php if ( ! empty( $wp_http_referer ) && ( true === $updated ) ) : ?>

			<p><a href="<?php echo esc_url( $wp_http_referer ); ?>"><?php esc_html_e( '&larr; Back to Users', 'wp-user-profiles' ); ?></a></p>

		<?php endif; ?>

		<?php if ( true === $dismissible ) : ?>

			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-user-profiles' ); ?></span>
			</button>

		<?php endif; ?>

	</div>

	<?php
}

/**
 * Create the Profile navigation in Edit User & Edit Profile pages.
 *
 * @since 0.1.0
 *
 * @param  object|null  $user     User to create profile navigation for.
 * @param  string       $current  Which profile to highlight.
 *
 * @return string
 */
function wp_user_profiles_admin_nav( $user = null ) {

	// Bail if no user ID exists here
	if ( empty( $user->ID ) ) {
		return;
	}

	// User admin is a special case where top-level menus replace
	// tabulated ones.
	if ( is_user_admin() ) {
		return;
	}

	// Add the user ID to query arguments when not editing yourself
	$query_args = ! IS_PROFILE_PAGE
		? array( 'user_id' => $user->ID )
		: array();

	// Conditionally add a referer if it exists in the existing request
	if ( ! empty( $_REQUEST['wp_http_referer'] ) ) {
		$query_args['wp_http_referer'] = urlencode( stripslashes_deep( $_REQUEST['wp_http_referer'] ) );
	}

	// Current page?
	$current = ! empty( $_GET['page'] )
		? sanitize_key( $_GET['page'] )
		: 'profile';

	// Get tabs
	$tabs     = wp_user_profiles_sections();
	$user_url = wp_user_profiles_edit_user_url_filter(); ?>

	<h2 id="profile-nav" class="nav-tab-wrapper">

		<?php foreach ( $tabs as $tab ) : ?>

			<?php if ( current_user_can( $tab->cap, $user->ID ) ) :
				$query_args['page'] = $tab->slug; ?>

				<a class="nav-tab<?php echo ( $tab->id === $current ) ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( $query_args, $user_url ) );?>">
					<?php echo esc_html( $tab->name ); ?>
				</a>

			<?php endif; ?>

		<?php endforeach; ?>

	</h2>

	<?php
}

/**
 * Title action links
 *
 * @since 0.1.0
 */
function wp_user_profiles_title_actions() {
	$add_url = is_network_admin()
		? network_adminrl( 'user-new.php' )
		: admin_url( 'user-new.php' );

	if ( current_user_can( 'create_users' ) ) : ?>

		<a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action"><?php echo esc_html_x( 'Add New', 'user', 'wp-user-profiles' ); ?></a>

	<?php elseif ( is_multisite() && current_user_can( 'promote_users' ) ) : ?>

		<a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action"><?php echo esc_html_x( 'Add Existing', 'user', 'wp-user-profiles' ); ?></a>

	<?php endif;
}

/**
 * Display the user's profile.
 *
 * @since 0.1.0
 */
function wp_user_profiles_user_admin() {

	// Reset a bunch of global values
	wp_reset_vars( array( 'action', 'user_id', 'wp_http_referer' ) );

	// Get the user ID
	$user_id = ! empty( $_GET['user_id'] )
		? (int) $_GET['user_id']
		: get_current_user_id();

	// Get user
	$user = get_user_to_edit( $user_id );

	/**
	 * Backwards compatibility for JIT metaboxes
	 *
	 * @since 0.2.0 Use `wp_user_profiles_add_meta_boxes` instead
	 */
	do_action( 'add_meta_boxes', get_current_screen()->id, $user );

	// Remove possible query arguments
	$request_url = remove_query_arg( array( 'action', 'error', 'updated', 'spam', 'ham' ), $_SERVER['REQUEST_URI'] );

	// Setup form action URL
	$form_action_url = add_query_arg( array(
		'action' => 'update'
	), $request_url );

	// Arbitrary notice execution point
	do_action( 'wp_user_profiles_admin_notices' ); ?>

	<div class="wrap" id="wp-user-profiles-page">
		<h1><?php

			// The page title
			echo esc_html( $user->display_name );

			// Any arbitrary "page-title-action" class links
			do_action( 'wp_user_profiles_title_actions' );

		?></h1>

		<?php wp_user_profiles_admin_nav( $user ); ?>

		<form action="<?php echo esc_url( $form_action_url ); ?>" id="your-profile" method="post" novalidate="novalidate" <?php do_action( 'user_edit_form_tag' ); ?>>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( get_current_screen()->id, 'side', $user ); ?>
					</div>

					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( get_current_screen()->id, 'normal',   $user ); ?>
						<?php do_meta_boxes( get_current_screen()->id, 'advanced', $user ); ?>
					</div>
				</div>
			</div>

			<input type="hidden" name="checkuser_id" value="<?php echo get_current_user_id(); ?>" />

			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order',  'meta-box-order-nonce', false ); ?>
			<?php wp_nonce_field( 'update-user_' . $user->ID ); ?>

		</form>
	</div><!-- .wrap -->

	<?php
}
