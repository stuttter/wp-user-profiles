<?php
/**
 * Sites section permission regression tests.
 *
 * @package WP_User_Profiles
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/sections/sites.php';
require_once __DIR__ . '/fixtures/site-membership-stubs.php';

/**
 * Test the Sites section's membership boundary.
 */
class SitesPermissionsTest extends TestCase {
	/**
	 * Reset test doubles before each case.
	 */
	protected function setUp(): void {
		wpup_test_reset();
	}

	/**
	 * Construct a user with database fields for the shared save routine.
	 *
	 * @return WP_User
	 */
	private function user(): WP_User {
		$user       = new WP_User( 9 );
		$user->data = (object) array( 'ID' => 9 );
		return $user;
	}

	/**
	 * Create the Sites section for a save test.
	 *
	 * @return WP_User_Profile_Sites_Section
	 */
	private function section(): WP_User_Profile_Sites_Section {
		$section         = new WP_User_Profile_Sites_Section();
		$section->id     = 'sites';
		$section->errors = new WP_Error();
		return $section;
	}

	/**
	 * A self-service profile save cannot grant a role on another site.
	 */
	public function test_user_cannot_add_self_to_site(): void {
		$_POST['action']   = 'add_as_administrator';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertArrayNotHasKey( 'add_user_to_blog', $GLOBALS['wpup_test']['calls'] );
	}

	/**
	 * A self-service profile save cannot remove a site member.
	 */
	public function test_user_cannot_remove_site_member(): void {
		$_POST['action']   = 'remove';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertArrayNotHasKey( 'remove_user_from_blog', $GLOBALS['wpup_test']['calls'] );
	}

	/**
	 * Network site managers retain the membership controls.
	 */
	public function test_network_manager_can_assign_site_role(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites'] = true;

		$_POST['action']   = 'add_as_editor';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertSame( array( array( 2, 9, 'editor' ) ), $GLOBALS['wpup_test']['calls']['add_user_to_blog'] );
	}

	/**
	 * A crafted primary site must belong to the edited user.
	 */
	public function test_primary_site_must_be_a_membership(): void {
		$GLOBALS['wpup_test']['user_blogs'][9] = array( 2 => (object) array( 'userblog_id' => 2 ) );

		$_POST['primary_blog'] = '3';

		$user = $this->user();
		$this->section()->save( $user );

		$this->assertArrayNotHasKey( 'update_user_meta', $GLOBALS['wpup_test']['calls'] );
		$this->assertFalse( isset( $user->data->primary_blog ) );
	}

	/**
	 * A member can still select an existing site as their primary site.
	 */
	public function test_member_can_select_primary_site(): void {
		$GLOBALS['wpup_test']['user_blogs'][9] = array( 2 => (object) array( 'userblog_id' => 2 ) );

		$_POST['primary_blog'] = '2';

		$this->section()->save( $this->user() );

		$this->assertSame( array( array( 9, 'primary_blog', 2 ) ), $GLOBALS['wpup_test']['calls']['update_user_meta'] );
	}
}
