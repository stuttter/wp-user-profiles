<?php

use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SaveRoutingTest extends TestCase {
	protected function setUp(): void {
		global $pagenow;
		wpup_test_reset();
		$pagenow = 'users.php';
		$user = new WP_User( 7 );
		$GLOBALS['wpup_test']['users'][7] = $user;
		$GLOBALS['wp_user_profile_sections'] = array(
			'profile' => (object) array( 'id' => 'profile', 'slug' => 'profile', 'order' => 1 ),
		);
		$GLOBALS['wpup_test']['nonce_valid'] = true;
		$GLOBALS['wpup_test']['capabilities']['edit_user'] = true;
		$_GET = array( 'page' => 'profile' );
		$_POST = array( 'user_id' => 7, 'action' => 'update' );
		$_REQUEST = array( 'page' => 'profile' );
	}

	public function test_invalid_nonce_stops_before_save_hooks(): void {
		$GLOBALS['wpup_test']['nonce_valid'] = false;
		try {
			wp_user_profiles_save_user();
			$this->fail( 'Expected the nonce check to stop the request.' );
		} catch ( Wpup_Die_Exception $exception ) {
			$this->assertStringContainsString( 'update-user_7', $exception->getMessage() );
			$this->assertArrayNotHasKey( 'do_action:personal_options_update', $GLOBALS['wpup_test']['calls'] );
			$this->assertArrayNotHasKey( 'do_action:edit_user_profile_update', $GLOBALS['wpup_test']['calls'] );
		}
	}

	public function test_role_input_is_removed_without_promote_users(): void {
		$_POST['role'] = array( 1 => 'administrator' );
		$GLOBALS['wpup_test']['capabilities']['edit_user'] = false;
		wp_user_profiles_save_user();
		$this->assertArrayNotHasKey( 'role', $_POST );
		$this->assertCount( 1, $GLOBALS['wpup_test']['calls']['check_admin_referer'] );
	}

	public function test_self_save_uses_personal_hook_and_redirects(): void {
		$GLOBALS['wpup_test']['current_user_id'] = 7;
		add_filter( 'wp_is_profile_page', function () { return true; } );
		try {
			wp_user_profiles_save_user();
			$this->fail( 'Expected a redirect.' );
		} catch ( Wpup_Redirect_Exception $exception ) {
			$this->assertArrayHasKey( 'do_action:personal_options_update', $GLOBALS['wpup_test']['calls'] );
			$this->assertArrayNotHasKey( 'do_action:edit_user_profile_update', $GLOBALS['wpup_test']['calls'] );
			$this->assertStringContainsString( 'updated=true', $exception->location );
			$this->assertStringContainsString( 'page=profile', $exception->location );
		}
	}

	public function test_other_user_save_uses_edit_hook_and_preserves_referrer(): void {
		$GLOBALS['wpup_test']['current_user_id'] = 1;
		$_REQUEST['wp_http_referer'] = '/wp-admin/users.php';
		add_filter( 'wp_is_profile_page', function () { return false; } );
		try {
			wp_user_profiles_save_user();
			$this->fail( 'Expected a redirect.' );
		} catch ( Wpup_Redirect_Exception $exception ) {
			$this->assertArrayHasKey( 'do_action:edit_user_profile_update', $GLOBALS['wpup_test']['calls'] );
			$this->assertArrayNotHasKey( 'do_action:personal_options_update', $GLOBALS['wpup_test']['calls'] );
			$this->assertStringContainsString( 'wp_http_referer=', $exception->location );
		}
	}
}
