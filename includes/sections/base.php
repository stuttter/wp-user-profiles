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
	public $cap = 'edit_user';

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
	 *
	 * @since 0.2.0
	 * @return type
	 */
	public function __construct() {

		// Bail if no args
		if ( empty( func_num_args() ) ) {
			return;
		}

		// Get function arguments
		$args = func_get_args();

		// Bail if first arg is not an array
		if ( ! is_array( $args[0] ) || empty( $args[0] ) ) {
			return;
		}

		// Maybe return section if already set
		if ( is_string( $args[0] ) && isset( $GLOBALS['wp_user_profile_sections'][ $args[0] ] ) ) {
			return $GLOBALS['wp_user_profile_sections'][ $args[0] ];
		}

		// Bail if required fields are missing
		if ( empty( $args[0]['id'] ) || empty( $args[0]['slug'] ) || empty( $args[0]['name'] ) || empty( $args[0]['icon'] ) ) {
			return;
		}

		// Set object properties
		$this->id    = isset( $args[0]['id']    ) ? $args[0]['id']    : '';
		$this->slug  = isset( $args[0]['slug']  ) ? $args[0]['slug']  : '';
		$this->name  = isset( $args[0]['name']  ) ? $args[0]['name']  : '';
		$this->icon  = isset( $args[0]['icon']  ) ? $args[0]['icon']  : '';
		$this->order = isset( $args[0]['order'] ) ? $args[0]['order'] : '';
		$this->cap   = isset( $args[0]['cap']   ) ? $args[0]['cap']   : '';

		// Setup the profile section
		$GLOBALS['wp_user_profile_sections'][ $this->id ] = $this;

		// Saving
		add_action( 'wp_user_profiles_save', array( $this, 'action_save' ) );

		// Meta Boxes
		add_action( 'wp_user_profiles_add_meta_boxes', array( $this, 'action_add_meta_boxes' ), 10, 2 );
	}

	/**
	 * Saving this section?
	 *
	 * @since 0.2.0
	 *
	 * @return type
	 */
	public function action_save( $user_id = 0 ) {

		// Bail if ID is empty
		if ( empty( $this->id ) || empty( $user_id ) ) {
			return;
		}

		// Current page?
		$current = ! empty( $_GET['page'] )
			? sanitize_key( $_GET['page'] )
			: 'profile';

		// Bail if not saving this section
		if ( $current !== $this->slug ) {
			return;
		}

		// Setup the user being saved
		$user     = new stdClass;
		$user->ID = (int) $user_id;

		// Do the save action
		return $this->save( $user );
	}

	/**
	 * Meta boxes for this section?
	 *
	 * @since 0.2.0
	 *
	 * @param string $type
	 * @param object $user
	 */
	public function action_add_meta_boxes( $type = '', $user = null ) {

		// Bail if ID is empty
		if ( empty( $this->id ) ) {
			return;
		}

		// Get types
		$types = wp_user_profiles_get_section_hooknames( $this->id );

		// Bail if not these metaboxes
		if ( ! in_array( $type, $types, true ) || ! current_user_can( $this->cap, $user->ID ) ) {
			return;
		}

		// Do the metabox action
		$this->add_meta_boxes( $type, $user );
	}

	/**
	 * Parent method for extended classes to call
	 *
	 * @since 0.2.0
	 *
	 * @param object $user
	 */
	public function save( $user = 0 ) {
		return do_action( "wp_user_profiles_save_{$this->id}_section", $user );
	}

	/**
	 * Parent method for extended classes to call
	 *
	 * @since 0.2.0
	 *
	 * @param string $type
	 * @param object $user
	 */
	public function add_meta_boxes( $type = '', $user = null ) {
		do_action( "wp_user_profiles_add_{$this->id}_meta_boxes", $type, $user );
	}
}
