<?php

/**
 * Triple J Software, Inc.
 *
 * This file exists to unobtrusively append a "Sponsor" link onto the end of the
 * array of plugin row-action links.
 *
 * You may permanently disable them by setting the JJJ_NO_SPONSOR constant.
 */
namespace JJJ\Plugins\Users\Profiles;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Add filters when admin initializes
add_action( 'admin_init', function() {

	// Bail if disabled
	if ( defined( 'JJJ_NO_SPONSOR' ) && JJJ_NO_SPONSOR ) {
		return;
	}

	// Plugin base name
	$basename = 'wp-user-profiles/wp-user-profiles.php';

	// Add filters
	add_filter( "plugin_action_links_{$basename}",               __NAMESPACE__ . '\\filter_plugin_action_links', 20 );
	add_filter( "network_admin_plugin_action_links_{$basename}", __NAMESPACE__ . '\\filter_plugin_action_links', 20 );
} );

/**
 * Filter plugin action links, and add a sponsorship link.
 *
 * @param array $actions
 * @return array
 */
function filter_plugin_action_links( $actions = array() ) {

	// Sponsor text
	$text = esc_html_x( 'Sponsor', 'verb', 'wp-user-profiles' );

	// Sponsor URL
	$url  = 'https://buy.stripe.com/7sI3cd2tK1Cy2lydQR';

	// Merge links & return
	return array_merge( $actions, array(
		'sponsor' => '<a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>'
	) );
}
