<?php

use PHPUnit\Framework\TestCase;

class MultisiteRolesTest extends TestCase {
	protected function setUp(): void {
		wpup_test_reset();
		$GLOBALS['wpup_test']['is_multisite'] = true;
		$GLOBALS['wpup_test']['current_blog_id'] = 1;
	}

	public function test_local_role_strategy_switches_and_restores_site(): void {
		$roles = wp_user_profiles_get_common_user_roles_local_strategy( 4 );
		$this->assertSame(
			array( 'administrator' => 'Administrator', 'editor' => 'Editor', 'subscriber' => 'Subscriber' ),
			$roles
		);
		$this->assertSame( 1, $GLOBALS['wpup_test']['current_blog_id'] );
		$this->assertSame( array( array( 4 ) ), $GLOBALS['wpup_test']['calls']['switch_to_blog'] );
		$this->assertCount( 1, $GLOBALS['wpup_test']['calls']['restore_current_blog'] );
	}

	public function test_permissions_save_updates_each_site_and_restores_context(): void {
		$GLOBALS['wpup_test']['capabilities']['edit_profile'] = true;
		$GLOBALS['wpup_test']['capabilities']['promote_user'] = true;
		$_GET['page'] = 'permissions';
		$_POST['role'] = array( 2 => 'editor', 3 => '' );
		$user = new WP_User( 9 );
		$section = new WP_User_Profile_Permissions_Section(
			array(
				'id' => 'permissions', 'slug' => 'permissions', 'cap' => 'edit_profile',
				'name' => 'Permissions', 'icon' => 'dashicons-hidden', 'order' => 1,
			)
		);
		$result = $section->save( $user );
		$this->assertSame( 9, $result );
		$this->assertSame( array( array( 'set', 'editor' ), array( 'remove' ) ), $user->role_history );
		$this->assertSame( array( 2, 1, 3, 1 ), $user->site_history );
		$this->assertSame( 1, $GLOBALS['wpup_test']['current_blog_id'] );
		$this->assertCount( 2, $GLOBALS['wpup_test']['calls']['restore_current_blog'] );
	}

	public function test_permissions_save_skips_site_without_promotion_capability(): void {
		$GLOBALS['wpup_test']['capabilities']['edit_profile'] = true;
		$GLOBALS['wpup_test']['capabilities']['promote_user'] = false;
		$_POST['role'] = array( 2 => 'administrator' );
		$user = new WP_User( 9 );
		$section = new WP_User_Profile_Permissions_Section(
			array(
				'id' => 'permissions', 'slug' => 'permissions', 'cap' => 'edit_profile',
				'name' => 'Permissions', 'icon' => 'dashicons-hidden', 'order' => 1,
			)
		);
		$section->save( $user );
		$this->assertSame( array(), $user->role_history );
		$this->assertSame( 1, $GLOBALS['wpup_test']['current_blog_id'] );
		$this->assertCount( 1, $GLOBALS['wpup_test']['calls']['restore_current_blog'] );
	}
}
