<?php

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/admin.php';

class Wpup_Modern_Styles_Test_Screen {
	public $id = 'users_page_profile';

	public function get_columns() {
		return 2;
	}
}

function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $url ) { return (string) $url; }
function wp_reset_vars( $vars ) {
	foreach ( $vars as $var ) {
		$GLOBALS[ $var ] = '';
	}
}
function get_current_screen() { return new Wpup_Modern_Styles_Test_Screen(); }
function remove_meta_box() { return true; }
function remove_query_arg( $keys, $url = '' ) { return (string) $url; }
function do_meta_boxes() { return true; }
function wp_nonce_field() { return ''; }

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ModernStylesTest extends TestCase {
	protected function setUp(): void {
		wpup_test_reset();
	}

	private function render_profile_page() {
		$user               = new stdClass();
		$user->ID           = 1;
		$user->display_name = 'Test User';
		$user->filter       = '';
		$GLOBALS['wpup_test']['users'][1] = $user;
		$_SERVER['REQUEST_URI'] = '/wp-admin/users.php?page=profile';

		ob_start();
		wp_user_profiles_user_admin();

		return ob_get_clean();
	}

	public function test_modern_styles_are_disabled_by_default(): void {
		$this->assertFalse( wp_user_profiles_use_modern_styles() );

		$html = $this->render_profile_page();

		$this->assertStringContainsString( 'class="wrap" id="wp-user-profiles-page"', $html );
		$this->assertStringNotContainsString( 'wp-user-profiles-modern', $html );
	}

	public function test_site_owner_can_enable_modern_styles_with_filter(): void {
		add_filter( 'wp_user_profiles_use_modern_styles', function () { return true; } );

		$this->assertTrue( wp_user_profiles_use_modern_styles() );

		$html = $this->render_profile_page();

		$this->assertStringContainsString( 'class="wrap wp-user-profiles-modern" id="wp-user-profiles-page"', $html );
	}
}
