<?php

use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SectionSaveTest extends TestCase {
	protected function setUp(): void {
		wpup_test_reset();
	}

	public function test_core_validation_hook_receives_and_updates_stdclass(): void {
		$section         = new WP_User_Profile_Section();
		$section->id     = 'account';
		$section->errors = new WP_Error();

		$user       = new WP_User( 7 );
		$user->data = (object) array(
			'ID'           => 7,
			'display_name' => 'Before validation',
		);

		add_action( 'user_profile_update_errors', function ( WP_Error &$errors, bool $update, stdClass &$user_data ) {
			$user_data->display_name = 'After validation';
		}, 10, 3 );

		$this->assertSame( 7, $section->save( $user ) );

		$arguments = $GLOBALS['wpup_test']['calls']['do_action:user_profile_update_errors'][0];
		$this->assertSame( $section->errors, $arguments[0] );
		$this->assertTrue( $arguments[1] );
		$this->assertInstanceOf( stdClass::class, $arguments[2] );
		$this->assertNotInstanceOf( WP_User::class, $arguments[2] );
		$this->assertSame( 7, $arguments[2]->ID );
		$this->assertSame( 'After validation', $arguments[2]->display_name );

		$updated_user = $GLOBALS['wpup_test']['calls']['wp_update_user'][0][0];
		$this->assertInstanceOf( stdClass::class, $updated_user );
		$this->assertSame( 'After validation', $updated_user->display_name );
		$this->assertSame( 7, $GLOBALS['wpup_test']['calls']['clean_user_cache'][0][0] );
	}
}
