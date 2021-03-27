<?php

/**
 * User Profile Admin
 *
 * @package Plugins/Users/Profiles/Admin
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register admin scripts
 *
 * @since 0.1.0
 */
function wp_user_profiles_admin_register_scripts() {

	// Set location & version for scripts & styles
	$src = wp_user_profiles_get_plugin_url();
	$ver = wp_user_profiles_get_asset_version();

	// Append CSS directory
	$src .= 'assets/css/';

	// Minify?
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$src .= 'min/';
	}

	// Right-to-Left?
	if ( is_rtl() ) {
		$src .= 'rtl/';
	} else {
		$src .= 'ltr/';
	}

	// Maybe add a trailing slash
	if ( ! empty( $src ) ) {
		$src = trailingslashit( $src );
	}

	// CSS Dependencies
	$deps = array( 'dashboard', 'dashicons', 'edit' );

	// Styles
	wp_register_style( 'wp-user-profiles', $src . 'user-profiles.css', $deps, $ver );
}

/**
 * Enqueue admin scripts
 *
 * Also, override a few scripts that we need to fork & maintain separately, as
 * they include minor tweaks for the changed markup.
 *
 * @since 0.1.0
 */
function wp_user_profiles_admin_enqueue_scripts() {

	// Set location & version for scripts & styles
	$src = wp_user_profiles_get_plugin_url();
	$ver = wp_user_profiles_get_asset_version();

	// Enqueue core scripts
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'dashboard' );

	// Styles
	wp_enqueue_style( 'wp-user-profiles' );

	// Replace the user-profile script with our own
	$handle = 'user-profile';
	$url    = $src . 'assets/js/user-profiles.js';
	$deps   = array( 'jquery', 'dashboard', 'password-strength-meter', 'wp-i18n', 'wp-util' );

	// Replace the user-profile script with our own
	wp_enqueue_script( $handle, $url, $deps, $ver );
	wp_scripts()->registered[ $handle ]->src  = $url;
	wp_scripts()->registered[ $handle ]->ver  = $ver;
	wp_scripts()->registered[ $handle ]->deps = $deps;
	wp_scripts()->set_translations( $handle );

	// Get the user ID
	$user_id = ! empty( $_GET['user_id'] )
		? absint( $_GET['user_id'] )
		: get_current_user_id();

	// Only enqueue;
	if ( wp_user_profiles_user_supports( 'application-passwords', $user_id ) ) {

		// Replace the application-passwords script with our own
		$handle = 'application-passwords';
		$url    = $src . 'assets/js/app-passwords.js';
		$deps   = array( 'jquery', 'wp-util', 'wp-api-request', 'wp-date', 'wp-i18n', 'wp-hooks' );

		// Replace the application-passwords script with our own
		wp_enqueue_script( $handle, $url, $deps, $ver, true );
		wp_scripts()->registered[ $handle ]->src  = $url;
		wp_scripts()->registered[ $handle ]->ver  = $ver;
		wp_scripts()->registered[ $handle ]->deps = $deps;
		wp_scripts()->set_translations( $handle );
	}
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
	$callback = 'wp_user_profiles_user_admin';

	// Add a visbile "Your Profile" link
	if ( 'users.php' === $file ) {

		// Remove the core "Your Profile" submenu
		unset( $GLOBALS['submenu']['users.php'][15] );

		// Add (and quickly remove) submenu pages
		foreach ( $sections as $nav ) {

			// Add the page
			$hook = add_submenu_page(
				$file,
				$nav->name,
				$nav->name,
				$nav->cap,
				$nav->slug,
				$callback
			);

			// Add the actions
			wp_user_profiles_admin_menu_hooks( $hook );

			// Now remove the page, so they don't appear in the menu
			remove_submenu_page( $file, $nav->slug );
		}

		// Re-add new "Your Profile" submenu
		add_submenu_page(
			$file,
			esc_html__( 'Profile', 'wp-user-profiles' ),
			esc_html__( 'Profile', 'wp-user-profiles' ),
			'read',
			'profile',
			$callback
		);

	// User admin needs some coercing
	} elseif ( is_user_admin() ) {

		// Remove the original page, so it doesn't appear in the menu
		remove_menu_page( 'profile.php' );

		// Loop through sections
		foreach ( $sections as $nav ) {

			// Top level menu
			if ( empty( $nav->parent ) ) {

				// Add the page
				$hook = add_menu_page(
					$nav->name,
					$nav->name,
					$nav->cap,
					$nav->slug,
					$callback,
					$nav->icon,
					$nav->order
				);

				// Add the actions
				wp_user_profiles_admin_menu_hooks( $hook );
			}

			// Get subsections
			$subsections = wp_user_profiles_filter_sections( array(
				'parent' => $nav->id
			) );

			// Maybe add subsections
			if ( ! empty( $subsections ) ) {

				// Loop through subsections
				foreach ( $subsections as $submenu ) {

					// Add the submenu page
					$hook = add_submenu_page(
						$nav->slug,
						$submenu->name,
						$submenu->name,
						$submenu->cap,
						$submenu->slug,
						$callback
					);

					// Add the actions
					wp_user_profiles_admin_menu_hooks( $hook );
				}
			}
		}

	// Blog admin, but "Profile Mode" is en effect
	} else {

		// Remove the original page, so it doesn't appear in the menu
		remove_menu_page( 'profile.php' );

		// Add the main page
		add_menu_page(
			esc_html__( 'Profile', 'wp-user-profiles' ),
			esc_html__( 'Profile', 'wp-user-profiles' ),
			'read',
			'profile',
			$callback,
			'dashicons-admin-users',
			5
		);

		// Loop through sections
		foreach ( $sections as $nav ) {

			// Add the submenu page
			$hook = add_submenu_page(
				$file,
				$nav->name,
				$nav->name,
				$nav->cap,
				$nav->slug,
				$callback
			);

			// Add the actions
			wp_user_profiles_admin_menu_hooks( $hook );
		}
	}
}

/**
 * Add the admin menu hooks
 *
 * @since 2.0.0
 *
 * @param string $hook
 */
function wp_user_profiles_admin_menu_hooks( $hook = '' ) {

	// Bail if hook is empty
	if ( empty( $hook ) ) {
		return;
	}

	// Add hooks
	add_action( "admin_head-{$hook}", 'wp_user_profiles_do_admin_head' );
	add_action( "load-{$hook}",       'wp_user_profiles_do_admin_load' );
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
 * Get the default query arguments.
 *
 * @since 2.0.0
 *
 * @param WP_User $user
 *
 * @return array
 */
function wp_user_profiles_admin_default_query_args( $user = null ) {

	// Default return value
	$retval = ! wp_is_profile_page()
		? array( 'user_id' => $user->ID )
		: array();

	// Conditionally add a referer if it exists in the existing request
	if ( ! empty( $_REQUEST['wp_http_referer'] ) ) {
		$retval['wp_http_referer'] = urlencode( stripslashes_deep( $_REQUEST['wp_http_referer'] ) );
	}

	return $retval;
}

/**
 * Get the current admin user profile page.
 *
 * @since 2.0.0
 *
 * @return string
 */
function wp_user_profiles_admin_current_page() {
	return ! empty( $_GET['page'] )
		? sanitize_key( $_GET['page'] )
		: 'profile';
}

/**
 * Whether or not to show a sub-navigation.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function wp_user_profiles_admin_show_subnav( $parent = '' ) {

	// Default return value
	$retval = false;

	// Get all sections
	$sections = ! empty( $parent )
		? wp_user_profiles_filter_sections( array( 'parent' => $parent ) )
		: wp_user_profiles_sections();

	// Loop through sections looking for any children
	foreach ( $sections as $section ) {
		if ( ! empty( $section->parent ) ) {
			$retval = true;
		}
	}

	// Filter & return
	return (bool) apply_filters( 'wp_user_profiles_admin_show_subnav', $retval );
}

/**
 * Create the Profile navigation in Edit User & Edit Profile pages.
 *
 * @since 0.1.0
 *
 * @param object|null $user User to create profile navigation for.
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
	$query_args = wp_user_profiles_admin_default_query_args( $user );

	// Current page?
	$current    = wp_user_profiles_admin_current_page();

	// Get sections
	$section    = wp_user_profiles_filter_sections( array(
		'id' => $current
	) );

	// Get sections
	$sections   = wp_user_profiles_filter_sections( array(
		'parent' => null
	) );

	// Get the base profile URL
	$user_url   = wp_user_profiles_edit_user_url_filter();

	// Start a buffer
	ob_start();

	// Loop through sections
	foreach ( $sections as $nav ) {

		// Maybe skip if user cannot view it
		if ( ! current_user_can( $nav->cap, $user->ID ) ) {
			continue;
		}

		// Nav URL
		$query_args['page'] = $nav->slug;
		$url                = add_query_arg( $query_args, $user_url );

		// Default class & aria
		$class = $aria = '';

		// Current
		if ( ( $nav->id === $current ) || ( $nav->id === $section->parent ) ) {
			$class = ' nav-tab-active';
			$aria  = ' aria-current="page"';
		}

		// Output the link
		?><a class="nav-tab<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $url );?>"<?php echo $aria; // Do not escape ?>><?php

			/**
			 * This text is intentionally not escaped to allow HTML, for
			 * things like badges, labels, or whatever else.
			 *
			 * Please make sure to escape your own strings as needed.
			 */
			echo apply_filters( 'wp_user_profiles_admin_nav_html', $nav->name, $nav );

		?></a><?php
	}

	// Get the links
	$links = ob_get_clean();

	// Output the navigation
	?><nav id="profile-nav" class="nav-tab-wrapper" aria-label="<?php esc_html_e( 'Secondary menu', 'wp-user-profiles' ); ?>"><?php

		// Output the links
		echo $links;

	?></nav><?php
}

/**
 * Output the secondary options page navigation
 *
 * @since 2.0.0
 *
 * @param object|null $user User to create profile navigation for.
 *
 * @return string
 */
function wp_user_profiles_admin_subnav( $user = null ) {

	// Bail if no user ID exists here
	if ( empty( $user->ID ) ) {
		return;
	}

	// User admin is a special case where top-level menus replace tabulated ones.
	if ( is_user_admin() ) {
		return;
	}

	// Bail if not showing any subsections
	if ( ! wp_user_profiles_admin_show_subnav() ) {
		return;
	}

	// Add the user ID to query arguments when not editing yourself
	$query_args = wp_user_profiles_admin_default_query_args( $user );

	// Get the current page
	$page       = wp_user_profiles_admin_current_page();

	// Get the current section
	$section    = wp_user_profiles_filter_sections( array(
		'id' => $page
	) );

	// Bail if section cannot be found
	if ( empty( $section ) ) {
		return;
	}

	// Which section to prepend (parent, or current)
	$prepend = ! empty( $section->parent )
		? wp_user_profiles_filter_sections( array( 'id' => $section->parent ) )
		: $section;

	// Get subsections
	$subsections = wp_user_profiles_filter_sections( array(
		'parent' => $prepend->id
	) );

	// Prepend the parent section to the beginning of the subsections
	array_unshift( $subsections, $prepend );

	// Get the base profile URL
	$user_url = wp_user_profiles_edit_user_url_filter();

	// Start a buffer
	ob_start();

	// Loop through sections
	foreach ( $subsections as $sub ) {

		// Maybe skip if user cannot view it
		if ( ! current_user_can( $sub->cap, $user->ID ) ) {
			continue;
		}

		// Get text to output
		$text = ! empty( $sub->parent )
			? $sub->name
			: $sub->subname;

		// Fallback if empty
		if ( empty( $text ) ) {
			$text = esc_html__( 'General', 'wp-user-profiles' );
		}

		// Nav URL
		$query_args['page'] = $sub->id;
		$url                = add_query_arg( $query_args, $user_url );

		// Default class & aria
		$class = $aria = '';

		// Current
		if ( $page === $sub->id ) {
			$class = ' current';
			$aria  = ' aria-current="page"';
		}

		// Output the link
		?><li class="<?php echo esc_attr( $class ); ?>"<?php echo $aria; // Do not escape ?>>
			<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $url ); ?>"><?php

				/**
				 * This text is intentionally not escaped to allow HTML, for
				 * things like badges, labels, or whatever else.
				 *
				 * Please make sure to escape your own strings as needed.
				 */
				echo apply_filters( 'wp_user_profiles_admin_subnav_html', $text, $sub );

			?></a>
		<li><?php
	}

	// Get links
	$links = ob_get_clean();

	// Bail if no links (user not capable even though sections exist)
	if ( empty( $links ) ) {
		return;
	}

	// Output the subnavigation
	?><ul id="profile-subnav" class="subsubsub"><?php

		// Output the links
		 echo $links;

	?></ul><?php
}

/**
 * Title action links
 *
 * @since 0.1.0
 */
function wp_user_profiles_title_actions() {
	$add_url = is_network_admin()
		? network_admin_url( 'user-new.php' )
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
	global $user_id, $user_can_edit;

	// Reset some global values
	wp_reset_vars( array( 'action', 'wp_http_referer' ) );

	// Get user to edit
	$user = wp_user_profiles_get_user_to_edit();

	// User ID
	$user_id = ! empty( $user )
		? $user->ID
		: 0;

	// Compatibility with Classic Editor plugin
	// See: https://github.com/WordPress/classic-editor/issues/158
	$user_can_edit = current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' );

	/**
	 * Backwards compatibility for JIT metaboxes
	 *
	 * @since 0.2.0 Use `wp_user_profiles_add_meta_boxes` instead
	 */
	do_action( 'add_meta_boxes', get_current_screen()->id, $user );

	// Remove the Classic Editor metabox
	remove_meta_box( 'classic-editor-switch-editor', null, 'side' );

	// Remove possible query arguments
	$request_url = remove_query_arg( array(
		'action',
		'error',
		'updated',
		'spam',
		'ham'
	), $_SERVER['REQUEST_URI'] );

	// Setup form action URL
	$form_action_url = add_query_arg( array(
		'action' => 'update'
	), $request_url );

	// Display name
	$display_name = ! empty( $user )
		? $user->display_name
		: esc_html__( 'Anonymous', 'wp-user-profiles' );

	// Columns
	$columns = ( 1 === (int) get_current_screen()->get_columns() )
		? '1'
		: '2'; ?>

	<div class="wrap" id="wp-user-profiles-page">
		<h1 class="wp-heading-inline"><?php

			// The page title
			echo esc_html( $display_name );

			// Any arbitrary "page-title-action" class links
			do_action( 'wp_user_profiles_title_actions' );

		?></h1>

		<hr class="wp-header-end"><?php

		// Notices underneath H1 to avoid screen jumpiness
		do_action( 'wp_user_profiles_admin_notices' );

		// Navigation wrapper
		?><div class="wp-user-profiles-nav-wrapper"><?php

			// All nav & subnav actions
			do_action( 'wp_user_profiles_nav_actions', $user );

		?></div>

		<form action="<?php echo esc_url( $form_action_url ); ?>" id="your-profile" method="post" novalidate="novalidate" <?php do_action( 'user_edit_form_tag' ); ?>>
			<div id="poststuff" class="poststuff">
				<div id="post-body" class="metabox-holder columns-<?php echo esc_attr( $columns ); ?>">
					<div id="postbox-container-1" class="postbox-container"><?php

						// Side
						do_meta_boxes( get_current_screen()->id, 'side', $user );

					?></div>

					<div id="postbox-container-2" class="postbox-container"><?php

						// Normal
						do_meta_boxes( get_current_screen()->id, 'normal',   $user );

						// Advanced
						do_meta_boxes( get_current_screen()->id, 'advanced', $user );

					?></div>
				</div>
			</div>

			<input type="hidden" name="checkuser_id" value="<?php echo get_current_user_id(); ?>" />

			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order',  'meta-box-order-nonce', false ); ?>
			<?php wp_nonce_field( 'update-user_' . $user_id ); ?>

		</form>
	</div><!-- .wrap -->

	<?php
}
