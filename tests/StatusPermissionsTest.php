<?php
/**
 * Status save permission regression tests.
 *
 * @package WP_User_Profiles
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/status.php';
require_once __DIR__ . '/fixtures/class-wpup-test-status-db.php';

/**
 * Test the status save authorization boundary.
 */
class StatusPermissionsTest extends TestCase {
	/**
	 * Reset test doubles before each case.
	 */
	protected function setUp(): void {
		wpup_test_reset();
		$GLOBALS['wpdb'] = new WPUP_Test_Status_DB();
	}

	/**
	 * A user cannot change their own status through a hidden field.
	 */
	public function test_self_profile_cannot_change_user_status(): void {
		$_POST['user_status'] = 'inactive';

		$user = new WP_User( 1 );

		wp_user_profiles_save_user_status( $user );

		$this->assertSame( array(), $GLOBALS['wpdb']->updates );
	}

	/**
	 * A user cannot change another account without edit permission.
	 */
	public function test_user_without_edit_permission_cannot_change_status(): void {
		$_POST['user_status'] = 'inactive';

		$user = new WP_User( 9 );

		wp_user_profiles_save_user_status( $user );

		$this->assertSame( array(), $GLOBALS['wpdb']->updates );
	}

	/**
	 * A manager can change the status of another user.
	 */
	public function test_user_manager_can_change_another_users_status(): void {
		$GLOBALS['wpup_test']['capabilities']['edit_user'] = true;

		$_POST['user_status'] = 'inactive';

		$user = new WP_User( 9 );

		wp_user_profiles_save_user_status( $user );

		$this->assertCount( 1, $GLOBALS['wpdb']->updates );
		$this->assertSame( array( 'ID' => 9 ), $GLOBALS['wpdb']->updates[0][2] );
	}
}
