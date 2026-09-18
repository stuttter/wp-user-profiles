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

	public function test_core_validation_hook_receives_stdclass(): void {
		$section         = new WP_User_Profile_Section();
		$section->id     = 'account';
		$section->errors = new WP_Error();

		$user = new WP_User( 7 );

		$this->assertSame( 7, $section->save( $user ) );

		$arguments = $GLOBALS['wpup_test']['calls']['do_action:user_profile_update_errors'][0];
		$this->assertSame( $section->errors, $arguments[0] );
		$this->assertTrue( $arguments[1] );
		$this->assertInstanceOf( stdClass::class, $arguments[2] );
		$this->assertNotInstanceOf( WP_User::class, $arguments[2] );
		$this->assertSame( 7, $arguments[2]->ID );
	}
}
