<?php

/**
 * User Profile Base Section
 *
 * @package Plugins/Users/Profiles/Sections/Base
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Primary User Profile Section class
 *
 * @since 0.2.0
 */
class WP_User_Profile_Section {

	/**
	 * Unique ID of the section
	 *
	 * @since 0.2.0
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Unique slug of the section
	 *
	 * @since 0.2.0
	 *
	 * @var string
	 */
	public $slug = '';

	/**
	 * Name of the section
	 *
	 * @since 0.2.0
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Capability used to ensure user can access the section
	 *
	 * @since 0.2.0
	 *
	 * @var string
	 */
	public $cap = 'edit_profile';

	/**
	 * Dashicon used to identify the section
	 *
	 * @since 0.2.0
	 *
	 * @var string
	 */
	public $icon = '';

	/**
	 * Numerical order for the section
	 *
	 * @since 0.2.0
	 *
	 * @var string
	 */
	public $order = '70';

	/**
	 * Parent page ID (if not a primary page)
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	public $parent = '';

	/**
	 * Name of the subsection (if prepended)
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	public $subname = '';

	/**
	 * Errors
	 *
	 * @since 0.2.0
	 *
	 * @var WP_Error
	 */
	public $errors = null;

	/**
	 * Maybe add new section to global
	 *
	 * @since 0.2.0
	 */
	public function __construct() {

		// Bail if no args
		if ( ! func_num_args() ) {
			return;
		}

		// Get first function argument
		$args = func_get_arg( 0 );

		// Setup if first arg is an array and not empty
		if ( is_array( $args ) || ! empty( $args ) ) {

			// Setup
			$this->setup( $args );

			// Add hooks
			$this->add_hooks();
		}
	}

	/**
	 * Setup the section.
	 *
	 * @since 2.5.1
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function setup( $args = array() ) {

		// Bail if no arguments
		if ( empty( $args ) || ! is_array( $args ) ) {
			return;
		}

		// Bail if required fields are missing
		if ( empty( $args['id'] ) || empty( $args['slug'] ) || empty( $args['name'] ) || empty( $args['icon'] ) ) {
			return;
		}

		// Setup the errors
		$this->errors = new WP_Error();

		// Set object properties
		$this->id      = isset( $args['id']      ) ? $args['id']      : '';
		$this->slug    = isset( $args['slug']    ) ? $args['slug']    : '';
		$this->name    = isset( $args['name']    ) ? $args['name']    : '';
		$this->icon    = isset( $args['icon']    ) ? $args['icon']    : '';
		$this->order   = isset( $args['order']   ) ? $args['order']   : '';
		$this->cap     = isset( $args['cap']     ) ? $args['cap']     : '';
		$this->parent  = isset( $args['parent']  ) ? $args['parent']  : '';
		$this->subname = isset( $args['subname'] ) ? $args['subname'] : '';

		// Setup the profile section
		$GLOBALS['wp_user_profile_sections'][ $this->id ] = $this;
	}

	/**
	 * Add actions and filters for this section.
	 *
	 * @since 2.5.1
	 */
	public function add_hooks() {

		// Bail if no section ID
		if ( empty( $this->id ) ) {
			return;
		}

		// Saving
		add_filter( 'wp_user_profiles_save', array( $this, 'action_save' ) );

		// Meta Boxes
		add_action( 'wp_user_profiles_add_meta_boxes', array( $this, 'action_add_meta_boxes' ), 10, 2 );

		// Contextual Help
		add_action( 'wp_user_profiles_add_contextual_help', array( $this, 'add_contextual_help' ) );
	}

	/**
	 * Saving this section?
	 *
	 * @since 0.2.0
	 *
	 * @return WP_User
	 */
	public function action_save( $user = null ) {

		// Bail if ID is empty
		if ( empty( $this->id ) ) {
			return $user;
		}

		// Bail if no page
		if ( empty( $_GET['page'] ) ) {
			return $user;
		}

		// Bail if not saving this section
		if ( sanitize_key( $_GET['page'] ) !== $this->slug ) {
			return $user;
		}

		// Do the save action
		return $this->save( $user );
	}

	/**
	 * Meta boxes for this section?
	 *
	 * @since 0.2.0
	 *
	 * @param string $type
	 * @param array  $args
	 */
	public function action_add_meta_boxes( $type = '', $args = array() ) {

		// Bail if ID is empty
		if ( empty( $this->id ) ) {
			return;
		}

		// Get hooknames
		$hookname = wp_user_profiles_get_section_hooknames( $this->id );

		// Maybe get user ID from array
		$user_id = ! empty( $args['user']->ID )
			? (int) $args['user']->ID
			: 0;

		// Bail if not these metaboxes
		if ( ( $hookname[0] !== $type ) || ! current_user_can( $this->cap, $user_id ) ) {
			return;
		}

		// Do the metabox action
		$this->add_meta_boxes( $type, $args );
	}

	/**
	 * Parent method for extended classes to call
	 *
	 * @since 0.2.0
	 *
	 * @param WP_User $user
	 * @return mixed Integer on success. WP_Error on failure.
	 */
	public function save( $user = null ) {

		// Allow third party plugins to hook into this sections saving process
		$user = apply_filters( "wp_user_profiles_save_{$this->id}_section", $user );

		// Return (do not update) if there are any errors
		if ( $this->errors->get_error_codes() || ( is_wp_error( $user ) && $user->get_error_codes() ) ) {
			return $this->errors;
		}

		// Pre-clean the cache before updating
		clean_user_cache( $user );

		// Update the user in the database
		return wp_update_user( $user );
	}

	/**
	 * Parent method for extended classes to call
	 *
	 * @since 0.2.0
	 *
	 * @param string $type
	 * @param array  $args
	 */
	public function add_meta_boxes( $type = '', $args = array() ) {
		do_action( "wp_user_profiles_add_{$this->id}_meta_boxes", $type, $args );
	}

	/**
	 * Contextual help for this section?
	 *
	 * @since 0.2.0
	 */
	public function add_contextual_help() {
		do_action( "wp_user_profiles_add_{$this->id}_contextual_help" );
	}
}
