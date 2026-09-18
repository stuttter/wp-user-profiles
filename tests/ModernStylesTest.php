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

	public function test_modern_styles_are_disabled_by_default(): void {
		$this->assertFalse( wp_user_profiles_use_modern_styles() );
	}

	public function test_site_owner_can_enable_modern_styles_with_filter(): void {
		add_filter( 'wp_user_profiles_use_modern_styles', function () { return true; } );

		$this->assertTrue( wp_user_profiles_use_modern_styles() );
	}
}
