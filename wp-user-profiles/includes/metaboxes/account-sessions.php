<?php

/**
 * User Profile Session Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Sessions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the session metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_session_metabox( $user = null ) {

	// Get session
	$sessions = WP_Session_Tokens::get_instance( $user->ID );
	$profile  = wp_is_profile_page();

	// Before
	do_action( __FUNCTION__ . '_before', $user ); ?>

	<table class="form-table">

		<?php if ( ( true === $profile ) && count( $sessions->get_all() ) === 1 ) : ?>

			<tr class="user-sessions-wrap hide-if-no-js">
				<th><?php _e( 'Sessions' ); ?></th>
				<td aria-live="assertive">
					<div class="destroy-sessions"><button type="button" disabled class="button button-secondary"><?php _e( 'Log Out Everywhere Else' ); ?></button></div>
					<p class="description">
						<?php _e( 'You are only logged in at this location.' ); ?>
					</p>
				</td>
			</tr>

		<?php elseif ( ( true === $profile ) && count( $sessions->get_all() ) > 1 ) : ?>

			<tr class="user-sessions-wrap hide-if-no-js">
				<th><?php _e( 'Sessions' ); ?></th>
				<td aria-live="assertive">
					<div class="destroy-sessions"><button type="button" class="button button-secondary" id="destroy-sessions"><?php _e( 'Log Out Everywhere Else' ); ?></button></div>
					<p class="description">
						<?php _e( 'Did you lose your phone or leave your account logged in at a public computer? You can log out everywhere else, and stay logged in here.' ); ?>
					</p>
				</td>
			</tr>

		<?php elseif ( ( false === $profile ) && $sessions->get_all() ) : ?>

			<tr class="user-sessions-wrap hide-if-no-js">
				<th><?php _e( 'Sessions' ); ?></th>
				<td>
					<p><button type="button" class="button button-secondary" id="destroy-sessions"><?php _e( 'Log Out Everywhere' ); ?></button></p>
					<p class="description">
						<?php
						/* translators: 1: User's display name. */
						printf( __( 'Log %s out of all locations.' ), $user->display_name );
						?>
					</p>
				</td>
			</tr>

		<?php elseif ( ( false === $profile ) && ! $sessions->get_all() ) : ?>

			<tr class="user-sessions-wrap hide-if-no-js">
				<th><?php _e( 'Inactive' ); ?></th>
				<td>
					<p>
						<?php
						/* translators: 1: User's display name. */
						printf( __( '%s has not logged in yet.' ), $user->display_name );
						?>
					</p>
				</td>
			</tr>

		<?php endif;

		do_action( 'wp_user_profiles_session_metabox', $user ); ?>

	</table>

	<?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
