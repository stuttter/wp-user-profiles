<?php

/**
 * User Profile Other Section
 *
 * @package Plugins/Users/Profiles/Sections/Other
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * User Profiles "Other" class
 *
 * @since 1.3.0
 */
class WP_User_Profile_Other_Section extends WP_User_Profile_Section {

	/**
	 * Add the meta boxes for this section
	 *
	 * @since 1.3.0
	 *
	 * @param string $type
	 * @param object $user
	 */
	public function add_meta_boxes( $type = '', $user = null ) {

		// Allow third party plugins to add metaboxes
		parent::add_meta_boxes( $type, $user );

		// Other
		add_meta_box(
			'other',
			_x( 'Additional', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_other_metabox',
			$type,
			'normal',
			'high',
			$user
		);
	}

	/**
	 * Save section data
	 *
	 * @since 1.3.0
	 *
	 * @param WP_User $user
	 */
	public function save( $user = null ) {

		// Allow third party plugins to save data in this section
		parent::save( $user );
	}

	/**
	 * Contextual help for this section
	 *
	 * @since 1.3.0
	 */
	public function add_contextual_help() {
		get_current_screen()->add_help_tab( array(
			'id'		=> $this->id,
			'title'		=> $this->name,
			'content'	=>
				'<p>'  . esc_html__( 'This is where any additional information can be found.', 'wp-user-profiles' ) . '</p><ul>' .
				'<li>' . esc_html__( 'Plugins using legacy actions',                           'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'Fields without explicit sections',                       'wp-user-profiles' ) . '</li></ul>'
		) );
	}
}
