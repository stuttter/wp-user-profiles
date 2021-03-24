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
	 */
	public function save( $user = null ) {

		// Primary Site
		$user->primary_blog = isset( $_POST['primary_blog'] )
			? (int) $_POST['primary_blog']
			: null;

		// Temporarily save this here, because it's not handled by WordPress
		if ( ! empty( $user->primary_blog ) ) {
			update_user_meta( $user->ID, 'primary_blog', $user->primary_blog );
		}

		// Update user sites membership through bulk actions
		if ( isset( $_POST['action'] ) && isset( $_POST['allblogs'] ) && is_array( $_POST['allblogs'] ) ) { // WPCS: input var ok
			$blog_ids = array_map( 'absint', (array) $_POST['allblogs'] ); // WPCS input var ok

			if ( 'remove' === $_POST['action'] ) { // WPCS: input var ok
				foreach ( $blog_ids as $blog_id ) {
					// TODO: Come up with a flow for reassigning content
					remove_user_from_blog( $user->ID, $blog_id );
				}
			} elseif ( false !== strpos( $_POST['action'], 'add_as_' ) ) { // WPCS: input var ok
				$role = substr_replace( $_POST['action'], '', 0, 7 ); // WPCS: input var ok

				foreach ( $blog_ids as $blog_id ) {
					// TODO Possibility of not hitting custom filters here ? maybe do it via REST ? :/ but then reachability concerns
					$_role = $role;
					if ( '__default__' === $role ) {
						$_role = get_blog_option( $blog_id, 'default_role' );
					}

					add_user_to_blog( $blog_id, $user->ID, $_role );
				}
			}
		}

		parent::save( $user );
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
