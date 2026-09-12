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
}
