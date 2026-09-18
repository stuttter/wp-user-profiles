<?php

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/admin.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ModernStylesTest extends TestCase {
	protected function setUp(): void {
		wpup_test_reset();
	}

	private function render_profile_page() {
		$user               = new WP_User( 1 );
		$user->display_name = 'Test User';
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
