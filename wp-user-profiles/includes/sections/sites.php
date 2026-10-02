<?php

/**
 * User Profile Sites Section
 *
 * @package Plugins/Users/Profiles/Sections/Sites
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * User Profiles "Sites" class
 *
 * @since 1.0.0
 */
class WP_User_Profile_Sites_Section extends WP_User_Profile_Section {

	/**
	 * Add the meta boxes for this section
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 * @param array  $args
	 */
	public function add_meta_boxes( $type = '', $args = array() ) {

		// Allow third party plugins to add metaboxes
		parent::add_meta_boxes( $type, $args );

		// Primary Site
		add_meta_box(
			'primary-site',
			_x( 'Primary Site', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_primary_site_metabox',
			$type,
			'normal',
			'high',
			$args
		);

		// Sites
		add_meta_box(
			'sites',
			_x( 'Sites', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_sites_metabox',
			$type,
			'normal',
			'core',
			$args
		);
	}

	/**
	 * Save section data
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $user
	 * @return mixed Integer on success. WP_Error on failure.
	 */
	public function save( $user = null ) {

		// Primary Site
		$primary_blog = isset( $_POST['primary_blog'] )
			? (int) $_POST['primary_blog']
			: 0;

		// Temporarily save this here, because it's not handled by WordPress
		if ( $primary_blog ) {
			$primary_site = get_site( $primary_blog );

			if ( ! $primary_site ) {
				return new WP_Error( 'primary_blog', esc_html__( 'The primary site you chose does not exist.', 'wp-user-profiles' ) );
			}

			$user->primary_blog = $primary_blog;
			update_user_meta( $user->ID, 'primary_blog', $primary_blog );
		}

		// Update user sites membership through bulk actions
		if ( current_user_can( 'manage_sites' ) && isset( $_POST['action'] ) && is_string( $_POST['action'] ) && isset( $_POST['allblogs'] ) && is_array( $_POST['allblogs'] ) ) { // WPCS: input var ok
			$blog_ids = array_map( 'absint', (array) $_POST['allblogs'] ); // WPCS input var ok
			// Preserve registered role slugs exactly; validate them on each site below.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$action = wp_unslash( $_POST['action'] );

			foreach ( $blog_ids as $blog_id ) {
				$site = get_site( $blog_id );

				if ( ! $site || ! can_edit_network( (int) $site->site_id ) ) {
					continue;
				}

				switch_to_blog( $blog_id );

				if ( 'remove' === $action && current_user_can( 'remove_users' ) ) {
					// TODO: Come up with a flow for reassigning content
					remove_user_from_blog( $user->ID, $blog_id );

				} elseif ( 0 === strpos( $action, 'add_as_' ) ) {
					$role = substr( $action, 7 );
					if ( '__default__' === $role ) {
						$role = get_blog_option( $blog_id, 'default_role' );
					}

					if ( wp_roles()->is_role( $role ) ) {
						$member = is_user_member_of_blog( $user->ID, $blog_id );
						if ( ! $member || (
							current_user_can( 'promote_users' )
							&& current_user_can( 'promote_user', $user->ID )
							&& ! empty( get_editable_roles()[ $role ] )
						) ) {
							add_user_to_blog( $blog_id, $user->ID, $role );
						}
					}
				}

				restore_current_blog();
			}
		}

		// Allow third party plugins to save data in this section
		return parent::save( $user );
	}

	/**
	 * Contextual help for this section
	 *
	 * @since 1.0.0
	 */
	public function add_contextual_help() {
		get_current_screen()->add_help_tab( array(
			'id'		=> $this->id,
			'title'		=> $this->name,
			'content'	=>
				'<p>'  . esc_html__( 'This is where sites & the primary set setting can be found.', 'wp-user-profiles' ) . '</p><ul>' .
				'<li>' . esc_html__( 'Your sites are determined by having a role on each one',      'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'You may be able to navigate between these sites',             'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'You may be able to take action on these sites',               'wp-user-profiles' ) . '</li></ul>'
		) );
	}
}
