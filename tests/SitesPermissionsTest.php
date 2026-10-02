<?php
/**
 * Sites section permission regression tests.
 *
 * @package WP_User_Profiles
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/sections/sites.php';
require_once __DIR__ . '/fixtures/class-wpup-test-site-roles.php';
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
	 * Primary site selection does not require site membership.
	 */
	public function test_primary_site_can_be_another_existing_site(): void {
		$_POST['primary_blog'] = '3';

		$this->section()->save( $this->user() );

		$this->assertSame( array( array( 9, 'primary_blog', 3 ) ), $GLOBALS['wpup_test']['calls']['update_user_meta'] );
	}

	/**
	 * Core rejects nonexistent primary sites.
	 */
	public function test_primary_site_must_exist(): void {
		$GLOBALS['wpup_test']['sites'][3] = false;

		$_POST['primary_blog'] = '3';

		$this->section()->save( $this->user() );

		$this->assertArrayNotHasKey( 'update_user_meta', $GLOBALS['wpup_test']['calls'] );
	}

	/**
	 * Network site management alone cannot remove a user from a site.
	 */
	public function test_removal_requires_remove_users(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites'] = true;

		$_POST['action']   = 'remove';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertArrayNotHasKey( 'remove_user_from_blog', $GLOBALS['wpup_test']['calls'] );
	}

	/**
	 * Network site managers with removal permission can remove membership.
	 */
	public function test_authorized_removal(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites'] = true;
		$GLOBALS['wpup_test']['capabilities']['remove_users'] = true;

		$_POST['action']   = 'remove';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertSame( array( array( 9, 2 ) ), $GLOBALS['wpup_test']['calls']['remove_user_from_blog'] );
	}

	/**
	 * Changing an existing member's role requires promotion permission.
	 */
	public function test_existing_member_role_change_requires_promotion(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites'] = true;

		$GLOBALS['wpup_test']['memberships'][9][2] = true;

		$_POST['action']   = 'add_as_administrator';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertArrayNotHasKey( 'add_user_to_blog', $GLOBALS['wpup_test']['calls'] );
		$this->assertSame( 1, $GLOBALS['wpup_test']['current_blog_id'] );
	}

	/**
	 * Existing membership also requires permission for the target user.
	 */
	public function test_existing_member_requires_target_promotion_permission(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites']  = true;
		$GLOBALS['wpup_test']['capabilities']['promote_users'] = true;
		$GLOBALS['wpup_test']['memberships'][9][2]             = true;

		$_POST['action']   = 'add_as_editor';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertArrayNotHasKey( 'add_user_to_blog', $GLOBALS['wpup_test']['calls'] );
		$this->assertSame( 1, $GLOBALS['wpup_test']['current_blog_id'] );
	}

	/**
	 * An authorized manager can change an existing member's role.
	 */
	public function test_authorized_existing_member_role_change(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites']  = true;
		$GLOBALS['wpup_test']['capabilities']['promote_users'] = true;
		$GLOBALS['wpup_test']['capabilities']['promote_user']  = true;
		$GLOBALS['wpup_test']['memberships'][9][2]             = true;

		$_POST['action']   = 'add_as_editor';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertSame( array( array( 2, 9, 'editor' ) ), $GLOBALS['wpup_test']['calls']['add_user_to_blog'] );
		$this->assertSame( 1, $GLOBALS['wpup_test']['current_blog_id'] );
	}

	/**
	 * Existing members cannot be given a role outside editable roles.
	 */
	public function test_existing_member_role_must_be_editable(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites']  = true;
		$GLOBALS['wpup_test']['capabilities']['promote_users'] = true;
		$GLOBALS['wpup_test']['capabilities']['promote_user']  = true;
		$GLOBALS['wpup_test']['memberships'][9][2]             = true;
		$GLOBALS['wpup_test']['editable_roles']                = array( 'editor' => array( 'name' => 'Editor' ) );

		$_POST['action']   = 'add_as_administrator';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertArrayNotHasKey( 'add_user_to_blog', $GLOBALS['wpup_test']['calls'] );
	}

	/**
	 * A site outside the manager's network cannot be changed.
	 */
	public function test_site_requires_network_access(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites'] = true;

		$GLOBALS['wpup_test']['can_edit_network'] = false;

		$_POST['action']   = 'add_as_editor';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertArrayNotHasKey( 'add_user_to_blog', $GLOBALS['wpup_test']['calls'] );
	}

	/**
	 * Bulk actions cannot target a nonexistent site.
	 */
	public function test_bulk_action_requires_existing_site(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites'] = true;
		$GLOBALS['wpup_test']['sites'][2]                     = false;

		$_POST['action']   = 'add_as_editor';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertArrayNotHasKey( 'add_user_to_blog', $GLOBALS['wpup_test']['calls'] );
	}

	/**
	 * A custom role slug keeps its case when adding a new member.
	 */
	public function test_custom_role_slug_is_preserved_for_new_member(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites'] = true;
		$GLOBALS['wpup_test']['all_roles']['Shop_Manager']    = true;

		$_POST['action']   = 'add_as_Shop_Manager';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertSame( array( array( 2, 9, 'Shop_Manager' ) ), $GLOBALS['wpup_test']['calls']['add_user_to_blog'] );
	}

	/**
	 * A custom editable role slug keeps its case for an existing member.
	 */
	public function test_custom_role_slug_is_preserved_for_existing_member(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites']   = true;
		$GLOBALS['wpup_test']['capabilities']['promote_users']  = true;
		$GLOBALS['wpup_test']['capabilities']['promote_user']   = true;
		$GLOBALS['wpup_test']['memberships'][9][2]              = true;
		$GLOBALS['wpup_test']['editable_roles']['Shop_Manager'] = array( 'name' => 'Shop Manager' );

		$_POST['action']   = 'add_as_Shop_Manager';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertSame( array( array( 2, 9, 'Shop_Manager' ) ), $GLOBALS['wpup_test']['calls']['add_user_to_blog'] );
	}

	/**
	 * A crafted action cannot add a member with an unregistered role.
	 */
	public function test_new_member_role_must_exist(): void {
		$GLOBALS['wpup_test']['capabilities']['manage_sites'] = true;

		$_POST['action']   = 'add_as_not_registered';
		$_POST['allblogs'] = array( 2 );

		$this->section()->save( $this->user() );

		$this->assertArrayNotHasKey( 'add_user_to_blog', $GLOBALS['wpup_test']['calls'] );
	}
}
