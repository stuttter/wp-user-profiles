<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Enqueue admin scripts
 *
 * @since 0.1.0
 */
function wp_user_profiles_admin_enqueue_scripts() {
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'user-profile' );
	wp_enqueue_script( 'dashboard' );

	// Set location & version for scripts & styles
	$src = wp_user_profiles_get_plugin_url();
	$ver = wp_user_profiles_get_asset_version();

	// Styles
	wp_enqueue_style( 'wp-user-profiles', $src . 'assets/css/user-profiles.css', array(), $ver );
}

/**
 * Add admin pages and setup submenus
 *
 * @since 0.1.0
 */
function wp_user_profiles_admin_menus() {

	// Remove the core "Your Profile" submenu
	unset( $GLOBALS['submenu']['users.php'][15] );

	// Empty hooks array
	$hooks = array();
	$file  = 'users.php';

	// Add (and quickly remove) submenu pages
	foreach ( wp_user_profiles_sections() as $tab ) {
		$hooks[] = add_submenu_page( 'users.php', $tab['name'], $tab['name'], $tab['cap'], $tab['slug'], 'wp_user_profiles_user_admin' );
		remove_submenu_page( $file, $tab['slug'] );
	}

	// Add a visbile "Your Profile" link
	add_submenu_page( $file, esc_html__( 'Your Profile', 'wp-user-profiles' ), esc_html__( 'Your Profile', 'wp-user-profiles' ), 'read', 'profile', 'wp_user_profiles_user_admin' );

	// Fudge the highlighted subnav item
	foreach( $hooks as $hook ) {
		add_action( "admin_head-{$hook}", 'wp_user_profiles_admin_menu_highlight' );
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

	// Get slugs from profile sections
	$plucked = wp_list_pluck( wp_user_profiles_sections(), 'slug' );

	// Maybe tweak the highlighted submenu
	if ( ! in_array( $plugin_page, array( $plucked ) ) ) {
		$submenu_file = 'profile';
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
	$notice = apply_filters( 'wp_user_profiles_get_admin_notices', array(), $wp_http_referer );

	if ( ! empty( $notice ) ) : ?>

		<div <?php if ( 'updated' === $notice['class'] ) : ?>id="message" <?php endif; ?>class="<?php echo esc_attr( $notice['class'] ); ?>">

			<p><?php echo esc_html( $notice['message'] ); ?></p>

			<?php if ( ! empty( $wp_http_referer ) && ( 'updated' === $notice['class'] ) ) : ?>

				<p><a href="<?php echo esc_url( $wp_http_referer ); ?>"><?php esc_html_e( '&larr; Back to Users', 'wp-user-profiles' ); ?></a></p>

			<?php endif; ?>

		</div>

	<?php endif;
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

	// Add the user ID to query arguments when not editing yourself
	if ( IS_PROFILE_PAGE ) {
		$query_args = array( 'user_id' => $user->ID );
	} else {
		$query_args = array();
	}

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

		<?php foreach ( $tabs as $tab_id => $tab ) : ?>

			<?php if ( current_user_can( $tab['cap'], $user->ID ) ) : ?>

				<a class="nav-tab<?php echo ( $tab_id === $current ) ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => $tab['slug'] ), $user_url ) );?>">
					<?php echo esc_html( $tab['name'] ); ?>
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
	if ( current_user_can( 'create_users' ) ) : ?>

		<a href="user-new.php" class="page-title-action"><?php echo esc_html_x( 'Add New', 'user', 'wp-user-profiles' ); ?></a>

	<?php elseif ( is_multisite() && current_user_can( 'promote_users' ) ) : ?>

		<a href="user-new.php" class="page-title-action"><?php echo esc_html_x( 'Add Existing', 'user', 'wp-user-profiles' ); ?></a>

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

	// Get current user ID
	$current_user = wp_get_current_user();
	if ( ! defined( 'IS_PROFILE_PAGE' ) ) {
		define( 'IS_PROFILE_PAGE', ( $user_id === $current_user->ID ) );
	}

	// User and/or title
	if ( empty( $user_id ) && IS_PROFILE_PAGE ) {
		$user_id = $current_user->ID;
	} elseif ( empty( $user_id ) && ! IS_PROFILE_PAGE ) {
		$title = esc_html__( 'Invalid user ID', 'wp-user-profiles' );
	} elseif ( ! get_userdata( $user_id ) ) {
		$title = esc_html__( 'Invalid user ID', 'wp-user-profiles' );
	}

	// Get user
	$user = get_user_to_edit( $user_id );

	// Setup meta boxes
	do_action( 'add_meta_boxes', get_current_screen()->id, $user );

	// Set title to user display name
	if ( ! empty( $user ) ) {
		$title = $user->display_name;
	}

	// Construct URL for form
	$request_url     = remove_query_arg( array( 'action', 'error', 'updated', 'spam', 'ham' ), $_SERVER['REQUEST_URI'] );
	$form_action_url = add_query_arg( 'action', 'update', $request_url );

	// Arbitrary notice execution point
	do_action( 'wp_user_profiles_admin_notices' ); ?>

	<div class="wrap" id="wp-user-profiles-page">
		<h1><?php

			// The page title
			echo esc_html( $title );

			// Any arbitrary "page-title-action" class links
			do_action( 'wp_user_profiles_title_actions' );

		?></h1>

		<?php if ( ! empty( $user ) ) :

			wp_user_profiles_admin_nav( $user ); ?>

			<form action="<?php echo esc_url( $form_action_url ); ?>" id="your-profile" method="post">
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

				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order',  'meta-box-order-nonce', false ); ?>
				<?php wp_nonce_field( 'edit-profile_' . $user->ID ); ?>

			</form>

		<?php else : ?>

			<p><?php esc_html_e( 'No user found with this ID.', 'wp-user-profiles' ); ?></p>

		<?php endif; ?>

	</div><!-- .wrap -->
	<?php
}
