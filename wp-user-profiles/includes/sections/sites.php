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
	 * @param  string  $type
	 * @param  WP_User $user
	 */
	public function add_meta_boxes( $type = '', $user = null ) {

		// Allow third party plugins to add metaboxes
		parent::add_meta_boxes( $type, $user );

		// Sites
		add_meta_box(
			'sites',
			_x( 'Sites', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_sites_metabox',
			$type,
			'normal',
			'high',
			$user
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

		// Role changes
		if ( isset( $_POST['sit'] ) && is_array( $_POST['role'] ) && current_user_can( $this->cap, $user->ID ) ) {

			// Loop through new roles
			foreach ( $_POST['role'] as $blog_id => $new_role ) {

				// Switch to the blog
				if ( is_multisite() ) {
					switch_to_blog( $blog_id );
				}

				// Only allow switching to to editable role for site
				$editable_roles = get_editable_roles();
				if ( ! empty( $new_role ) && ! empty( $editable_roles[ $new_role ] ) ) {
					$user->set_role( $new_role );
				}

				// Switch back
				if ( is_multisite() ) {
					restore_current_blog();
				}
			}
		}

		// Allow third party plugins to save data in this section
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
				'<p>'  . esc_html__( 'This is where role & capability settings can be found.', 'wp-user-profiles' ) . '</p><ul>' .
				'<li>' . esc_html__( 'Your role determines what you are able to do',           'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'In some cases, you may have more than one role',         'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'Some capabilities may be uniquely granted',              'wp-user-profiles' ) . '</li></ul>'
		) );
	}
}
