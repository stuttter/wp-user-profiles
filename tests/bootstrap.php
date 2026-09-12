<?php

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class Wpup_Redirect_Exception extends RuntimeException {
	public $location;

	public function __construct( $location ) {
		parent::__construct( 'Redirected to ' . $location );
		$this->location = $location;
	}
}

class Wpup_Die_Exception extends RuntimeException {
	public $payload;

	public function __construct( $payload ) {
		parent::__construct( is_string( $payload ) ? $payload : 'WordPress died.' );
		$this->payload = $payload;
	}
}

class WP_Error {
	private $codes = array();

	public function __construct( $code = '' ) {
		if ( $code ) {
			$this->codes[] = $code;
		}
	}

	public function get_error_codes() {
		return $this->codes;
	}
}

class WP_User {
	public $ID = 0;
	public $roles = array();
	public $filter = '';
	public $user_status = 0;
	public $site_history = array();
	public $role_history = array();

	public function __construct( $user = 0 ) {
		if ( $user instanceof self ) {
			foreach ( get_object_vars( $user ) as $key => $value ) {
				$this->$key = $value;
			}
		} elseif ( is_object( $user ) ) {
			foreach ( get_object_vars( $user ) as $key => $value ) {
				$this->$key = $value;
			}
		} else {
			$this->ID = (int) $user;
		}
	}

	public function for_site( $site_id ) {
		$this->site_history[] = (int) $site_id;
	}

	public function set_role( $role ) {
		$this->role_history[] = array( 'set', $role );
		$this->roles = array( $role );
	}

	public function remove_all_caps() {
		$this->role_history[] = array( 'remove' );
		$this->roles = array();
	}
}

$GLOBALS['wpup_test'] = array();

function wpup_test_reset() {
	$GLOBALS['wpup_test'] = array(
		'actions'           => array(),
		'filters'           => array(),
		'calls'             => array(),
		'current_user_id'   => 1,
		'logged_in'         => true,
		'is_admin'          => true,
		'is_multisite'      => false,
		'is_network_admin'  => false,
		'is_user_admin'     => false,
		'is_blog_admin'     => true,
		'current_blog_id'   => 1,
		'blog_stack'        => array(),
		'capabilities'      => array(),
		'users'             => array(),
		'editable_roles'    => array(
			'administrator' => array( 'name' => 'Administrator' ),
			'editor'        => array( 'name' => 'Editor' ),
			'subscriber'    => array( 'name' => 'Subscriber' ),
		),
	);
	$_GET = array();
	$_POST = array();
	$_REQUEST = array();
}

function wpup_test_call( $name, $arguments = array() ) {
	$GLOBALS['wpup_test']['calls'][ $name ][] = $arguments;
}

function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['wpup_test']['actions'][ $hook ][ $priority ][] = array( $callback, $accepted_args );
	return true;
}
function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['wpup_test']['filters'][ $hook ][ $priority ][] = array( $callback, $accepted_args );
	return true;
}
function remove_action( $hook, $callback, $priority = 10 ) { return true; }
function remove_filter( $hook, $callback, $priority = 10 ) { return true; }
function has_action( $hook ) { return ! empty( $GLOBALS['wpup_test']['actions'][ $hook ] ); }
function apply_filters( $hook, $value ) {
	$args = func_get_args();
	array_shift( $args );
	if ( empty( $GLOBALS['wpup_test']['filters'][ $hook ] ) ) {
		return $value;
	}
	ksort( $GLOBALS['wpup_test']['filters'][ $hook ] );
	foreach ( $GLOBALS['wpup_test']['filters'][ $hook ] as $callbacks ) {
		foreach ( $callbacks as $registration ) {
			$call_args = array_slice( $args, 0, $registration[1] );
			$value = call_user_func_array( $registration[0], $call_args );
			$args[0] = $value;
		}
	}
	return $value;
}
function do_action( $hook ) {
	$args = func_get_args();
	array_shift( $args );
	wpup_test_call( 'do_action:' . $hook, $args );
	if ( empty( $GLOBALS['wpup_test']['actions'][ $hook ] ) ) {
		return;
	}
	ksort( $GLOBALS['wpup_test']['actions'][ $hook ] );
	foreach ( $GLOBALS['wpup_test']['actions'][ $hook ] as $callbacks ) {
		foreach ( $callbacks as $registration ) {
			call_user_func_array( $registration[0], array_slice( $args, 0, $registration[1] ) );
		}
	}
}
function do_action_ref_array( $hook, $args ) { wpup_test_call( 'do_action:' . $hook, $args ); }

function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
function plugin_dir_url( $file ) { return 'https://example.test/plugins/' . basename( dirname( $file ) ) . '/'; }
function load_plugin_textdomain() { return true; }
function esc_html__( $text ) { return $text; }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) ); }
function absint( $value ) { return abs( (int) $value ); }
function wp_unslash( $value ) { return $value; }
function wp_parse_args( $args, $defaults = array() ) { return array_merge( $defaults, (array) $args ); }
function wp_list_pluck( $list, $field ) {
	$result = array();
	foreach ( $list as $key => $item ) {
		$result[ $key ] = is_object( $item ) ? $item->$field : $item[ $field ];
	}
	return $result;
}
function wp_list_filter( $list, $args ) {
	return array_filter( $list, function ( $item ) use ( $args ) {
		foreach ( $args as $key => $value ) {
			if ( ! isset( $item->$key ) || $item->$key !== $value ) {
				return false;
			}
		}
		return true;
	} );
}
function get_current_user_id() { return $GLOBALS['wpup_test']['current_user_id']; }
function is_user_logged_in() { return $GLOBALS['wpup_test']['logged_in']; }
function is_admin() { return $GLOBALS['wpup_test']['is_admin']; }
function is_multisite() { return $GLOBALS['wpup_test']['is_multisite']; }
function is_network_admin() { return $GLOBALS['wpup_test']['is_network_admin']; }
function is_user_admin() { return $GLOBALS['wpup_test']['is_user_admin']; }
function is_blog_admin() { return $GLOBALS['wpup_test']['is_blog_admin']; }
function get_current_blog_id() { return $GLOBALS['wpup_test']['current_blog_id']; }
function switch_to_blog( $site_id ) {
	$GLOBALS['wpup_test']['blog_stack'][] = $GLOBALS['wpup_test']['current_blog_id'];
	$GLOBALS['wpup_test']['current_blog_id'] = (int) $site_id;
	wpup_test_call( __FUNCTION__, array( (int) $site_id ) );
	return true;
}
function restore_current_blog() {
	$GLOBALS['wpup_test']['current_blog_id'] = array_pop( $GLOBALS['wpup_test']['blog_stack'] );
	wpup_test_call( __FUNCTION__ );
	return true;
}
function current_user_can( $capability ) {
	$args = func_get_args();
	wpup_test_call( __FUNCTION__, $args );
	$value = isset( $GLOBALS['wpup_test']['capabilities'][ $capability ] )
		? $GLOBALS['wpup_test']['capabilities'][ $capability ]
		: false;
	return is_callable( $value ) ? (bool) call_user_func_array( $value, array_slice( $args, 1 ) ) : (bool) $value;
}
function get_userdata( $user_id ) {
	return isset( $GLOBALS['wpup_test']['users'][ $user_id ] ) ? $GLOBALS['wpup_test']['users'][ $user_id ] : false;
}
function get_editable_roles() { return $GLOBALS['wpup_test']['editable_roles']; }
function check_admin_referer( $action ) {
	wpup_test_call( __FUNCTION__, func_get_args() );
	if ( empty( $GLOBALS['wpup_test']['nonce_valid'] ) ) {
		throw new Wpup_Die_Exception( 'Invalid nonce: ' . $action );
	}
	return 1;
}
function wp_die( $payload ) { throw new Wpup_Die_Exception( $payload ); }
function wp_safe_redirect( $location ) { throw new Wpup_Redirect_Exception( $location ); }
function get_edit_user_link( $user_id ) { return 'https://example.test/wp-admin/user-edit.php?user_id=' . (int) $user_id; }
function add_query_arg( $args, $url = '' ) {
	if ( ! is_array( $args ) ) {
		$args = array( $args => $url );
		$url = '';
	}
	$separator = false === strpos( $url, '?' ) ? '?' : '&';
	return $url . $separator . http_build_query( $args );
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function clean_user_cache() { return true; }
function wp_update_user( $user ) { return $user->ID; }
function get_edit_profile_url( $user_id ) { return 'https://example.test/profile/' . (int) $user_id; }

wpup_test_reset();

require_once dirname( __DIR__ ) . '/wp-user-profiles.php';
$GLOBALS['wpup_bootstrap_actions'] = $GLOBALS['wpup_test']['actions'];
require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/common.php';
require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/capabilities.php';
require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/sections/base.php';
require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/sections/permissions.php';
require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/metaboxes/sites-list.php';
