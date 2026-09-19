<?php

use PHPUnit\Framework\TestCase;

class BootstrapAndCapabilitiesTest extends TestCase {
	protected function setUp(): void {
		wpup_test_reset();
	}

	public function test_plugin_bootstrap_registers_the_loader(): void {
		$this->assertNotEmpty( $GLOBALS['wpup_bootstrap_actions']['plugins_loaded'] );
		$this->assertSame( '_wp_user_profiles', $GLOBALS['wpup_bootstrap_actions']['plugins_loaded'][10][0][0] );
	}

	public function test_edit_profile_maps_to_exist_for_current_user(): void {
		$GLOBALS['wpup_test']['current_user_id'] = 7;
		$caps = wp_user_profiles_map_meta_cap( array( 'edit_users' ), 'edit_profile', 7, array( 7 ) );
		$this->assertSame( array( 'exist' ), $caps );
	}

	public function test_invalid_user_is_rejected(): void {
		$this->expectException( Wpup_Die_Exception::class );
		$this->expectExceptionMessage( 'Invalid user ID.' );
		wp_user_profiles_current_user_can_edit( 99 );
	}

	public function test_user_without_edit_capability_is_rejected(): void {
		$GLOBALS['wpup_test']['users'][9] = new WP_User( 9 );
		$this->expectException( Wpup_Die_Exception::class );
		$this->expectExceptionMessage( 'Sorry, you are not allowed to edit this user.' );
		wp_user_profiles_current_user_can_edit( 9 );
	}

	public function test_user_with_edit_capability_is_allowed(): void {
		$GLOBALS['wpup_test']['users'][9] = new WP_User( 9 );
		$GLOBALS['wpup_test']['capabilities']['edit_user'] = true;
		$this->assertNull( wp_user_profiles_current_user_can_edit( 9 ) );
	}

	/**
	 * Core handles valid email confirmation requests.
	 */
	public function test_email_confirmation_request_uses_the_core_profile_route(): void {
		$_GET['newuseremail'] = 'confirmation-hash';

		$this->assertNull( wp_user_profiles_old_profile_redirect() );
	}

	/**
	 * Core handles pending email cancellation requests.
	 */
	public function test_email_change_cancellation_uses_the_core_profile_route(): void {
		$GLOBALS['wpup_test']['current_user_id'] = 7;
		$_GET['dismiss']                         = '7_new_email';

		$this->assertNull( wp_user_profiles_old_profile_redirect() );
	}

	/**
	 * Ordinary profile.php requests remain redirected.
	 */
	public function test_unrelated_profile_request_still_redirects(): void {
		$this->expectException( Wpup_Redirect_Exception::class );
		wp_user_profiles_old_profile_redirect();
	}

	/**
	 * An explicit query argument exposes Core's own profile screen.
	 */
	public function test_profile_redirect_can_be_bypassed(): void {
		$_GET['wpup-skip-redirect'] = '1';

		$this->assertNull( wp_user_profiles_old_profile_redirect() );
	}

	/**
	 * The bypass requires the exact documented value.
	 */
	public function test_profile_redirect_rejects_other_bypass_values(): void {
		$_GET['wpup-skip-redirect'] = 'yes';

		$this->expectException( Wpup_Redirect_Exception::class );
		wp_user_profiles_old_profile_redirect();
	}

	/**
	 * The same escape hatch applies when editing another user.
	 */
	public function test_user_edit_redirect_can_be_bypassed(): void {
		$_GET['wpup-skip-redirect'] = '1';

		$this->assertNull( wp_user_profiles_old_user_edit_redirect() );
	}
}
