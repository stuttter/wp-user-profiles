<?php
/**
 * Permissions section role-change regression tests.
 *
 * @package WP_User_Profiles
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/site-membership-stubs.php';

/**
 * Test role changes at the section's own authorization boundary.
 */
class PermissionsSecurityTest extends TestCase {
	/**
	 * Reset test doubles before each case.
	 */
	protected function setUp(): void {
		wpup_test_reset();
	}

	/**
	 * Build a section with self-profile access.
	 *
	 * @return WP_User_Profile_Permissions_Section
	 */
	private function section(): WP_User_Profile_Permissions_Section {
		$section         = new WP_User_Profile_Permissions_Section();
		$section->id     = 'permissions';
		$section->cap    = 'edit_profile';
		$section->errors = new WP_Error();
		return $section;
	}

	/**
	 * Build a user with database fields for the shared save routine.
	 *
	 * @return WP_User
	 */
	private function user(): WP_User {
		$user       = new WP_User( 9 );
		$user->data = (object) array( 'ID' => 9 );
		return $user;
	}

	/**
	 * Self-profile access does not authorize current-site role changes.
	 */
	public function test_current_site_role_requires_promotion_permission(): void {
		$GLOBALS['wpup_test']['is_multisite']                 = true;
		$GLOBALS['wpup_test']['capabilities']['edit_profile'] = true;
		$_POST['role']                                        = array( 1 => 'administrator' );

		$user = $this->user();
		$this->section()->save( $user );

		$this->assertSame( array(), $user->role_history );
	}

	/**
	 * Single-site role changes require promotion permission too.
	 */
	public function test_single_site_role_requires_promotion_permission(): void {
		$GLOBALS['wpup_test']['capabilities']['edit_profile'] = true;
		$_POST['role']                                        = array( 1 => 'administrator' );

		$user = $this->user();
		$this->section()->save( $user );

		$this->assertSame( array(), $user->role_history );
	}

	/**
	 * Authorized current-site role changes still work.
	 */
	public function test_current_site_role_changes_with_promotion_permission(): void {
		$GLOBALS['wpup_test']['is_multisite']                 = true;
		$GLOBALS['wpup_test']['capabilities']['edit_profile'] = true;
		$GLOBALS['wpup_test']['capabilities']['promote_user'] = true;
		$_POST['role']                                        = array( 1 => 'editor' );

		$user = $this->user();
		$this->section()->save( $user );

		$this->assertSame( array( array( 'set', 'editor' ) ), $user->role_history );
		$this->assertSame( 1, $GLOBALS['wpup_test']['current_blog_id'] );
	}

	/**
	 * A crafted self-profile request cannot change its own role.
	 */
	public function test_self_role_change_is_blocked(): void {
		$GLOBALS['wpup_test']['capabilities']['edit_profile'] = true;
		$GLOBALS['wpup_test']['capabilities']['promote_user'] = true;
		$_POST['role']                                        = array( 1 => 'subscriber' );

		$user       = new WP_User( 1 );
		$user->data = (object) array( 'ID' => 1 );
		$this->section()->save( $user );

		$this->assertSame( array(), $user->role_history );
	}

	/**
	 * A network administrator cannot change roles on another network.
	 */
	public function test_role_change_requires_network_access(): void {
		$GLOBALS['wpup_test']['is_multisite']                 = true;
		$GLOBALS['wpup_test']['capabilities']['edit_profile'] = true;
		$GLOBALS['wpup_test']['capabilities']['promote_user'] = true;
		$GLOBALS['wpup_test']['can_edit_network']             = false;
		$_POST['role']                                        = array( 2 => 'administrator' );

		$user = $this->user();
		$this->section()->save( $user );

		$this->assertSame( array(), $user->role_history );
		$this->assertSame( 1, $GLOBALS['wpup_test']['current_blog_id'] );
	}

	/**
	 * A role change cannot target a site that does not exist.
	 */
	public function test_role_change_requires_existing_site(): void {
		$GLOBALS['wpup_test']['is_multisite']                 = true;
		$GLOBALS['wpup_test']['capabilities']['edit_profile'] = true;
		$GLOBALS['wpup_test']['capabilities']['promote_user'] = true;
		$GLOBALS['wpup_test']['sites'][2]                     = false;
		$_POST['role']                                        = array( 2 => 'administrator' );

		$user = $this->user();
		$this->section()->save( $user );

		$this->assertSame( array(), $user->role_history );
		$this->assertSame( 1, $GLOBALS['wpup_test']['current_blog_id'] );
	}
}
