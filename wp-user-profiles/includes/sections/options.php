<?php

/**
 * User Profile Options Section
 *
 * @package Plugins/Users/Profiles/Sections/Options
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * User Profiles "Options" class
 *
 * @since 0.2.0
 */
class WP_User_Profile_Options_Section extends WP_User_Profile_Section {

	/**
	 * Add the meta boxes for this section
	 *
	 * @since 0.2.0
	 *
	 * @param string $type
	 * @param array  $args
	 */
	public function add_meta_boxes( $type = '', $args = array() ) {

		// Allow third party plugins to add metaboxes
		parent::add_meta_boxes( $type, $args );

		// Color schemes (only if available)
		if ( count( $GLOBALS['_wp_admin_css_colors'] ) && has_action( 'admin_color_scheme_picker' ) ) {
			add_meta_box(
				'colors',
				_x( 'Color Scheme', 'users user-admin edit screen', 'wp-user-profiles' ),
				'wp_user_profiles_color_scheme_metabox',
				$type,
				'normal',
				'high',
				$args
			);
		}

		// Personal options
		add_meta_box(
			'options',
			_x( 'Personal Options', 'users user-admin edit screen', 'wp-user-profiles' ),
			'wp_user_profiles_personal_options_metabox',
			$type,
			'normal',
			'core',
			$args
		);
	}

	/**
	 * Save section data
	 *
	 * @since 0.2.0
	 *
	 * @param WP_User $user
	 * @return mixed Integer on success. WP_Error on failure.
	 */
	public function save( $user = null ) {

		// Color Scheme
		$user->admin_color = isset( $_POST['admin_color'] )
			? sanitize_text_field( $_POST['admin_color'] )
			: 'fresh';

		// Double negative visual editor
		$user->rich_editing = isset( $_POST['rich_editing'] )
			? 'false'
			: 'true';

		// Admin bar front
		$user->show_admin_bar_front = isset( $_POST['admin_bar_front'] )
			? 'true'
			: 'false';

		// Enable comments shortcuts
		$user->comment_shortcuts = isset( $_POST['comment_shortcuts'] )
			? 'true'
			: 'false';

		// Force SSL
		$user->use_ssl = isset( $_POST['use_ssl'] )
			? 1
			: 0;

		// Allow third party plugins to save data in this section
		return parent::save( $user );
	}

	/**
	 * Contextual help for this section
	 *
	 * @since 0.2.0
	 */
	public function add_contextual_help() {
		get_current_screen()->add_help_tab( array(
			'id'		=> $this->id,
			'title'		=> $this->name,
			'content'	=>
				'<p>'  . esc_html__( 'This is where most options & site preferences can be found.', 'wp-user-profiles' ) . '</p><ul>' .
				'<li>' . esc_html__( 'Color Schemes',                                               'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'Visual Editor',                                               'wp-user-profiles' ) . '</li>' .
				'<li>' . esc_html__( 'Comment Moderation Shortcuts',                                'wp-user-profiles' ) . '</li></ul>'
		) );
	}
}
