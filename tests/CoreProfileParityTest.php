<?php

use PHPUnit\Framework\TestCase;

function get_available_languages() {
	return isset( $GLOBALS['wpup_test']['available_languages'] )
		? $GLOBALS['wpup_test']['available_languages']
		: array();
}

function is_email( $email ) {
	return false !== filter_var( $email, FILTER_VALIDATE_EMAIL );
}

function email_exists( $email ) {
	return isset( $GLOBALS['wpup_test']['email_owners'][ $email ] )
		? $GLOBALS['wpup_test']['email_owners'][ $email ]
		: false;
}

function wp_can_install_language_pack() {
	return ! empty( $GLOBALS['wpup_test']['can_install_language_pack'] );
}

function wp_download_language_pack( $locale ) {
	wpup_test_call( __FUNCTION__, array( $locale ) );
	return ! empty( $GLOBALS['wpup_test']['language_pack_downloads'][ $locale ] );
}

require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/sections/options.php';
require_once dirname( __DIR__ ) . '/wp-user-profiles/includes/sections/account.php';

class CoreProfileParityUser extends WP_User {
	public $admin_color = '';
	public $rich_editing = '';
	public $syntax_highlighting = '';
	public $infinite_scrolling = '';
	public $show_admin_bar_front = '';
	public $comment_shortcuts = '';
	public $use_ssl = 0;
	public $user_login = '';
	public $user_email = '';
	public $locale = '';
	public $user_pass = '';
}

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CoreProfileParityTest extends TestCase {
	protected function setUp(): void {
		wpup_test_reset();
		$GLOBALS['_wp_admin_css_colors'] = array();
	}

	public function test_options_match_current_core_update_semantics(): void {
		$_POST = array(
			'admin_color'          => 'ocean',
			'rich_editing'         => 'false',
			'syntax_highlighting'  => 'false',
			'infinite_scrolling'   => 'false',
			'admin_bar_front'      => '1',
			'comment_shortcuts'    => 'true',
			'use_ssl'              => '1',
		);

		$section         = new WP_User_Profile_Options_Section();
		$section->id     = 'options';
		$section->errors = new WP_Error();

		$this->assertSame( 7, $section->save( new CoreProfileParityUser( 7 ) ) );

		$updated_user = $GLOBALS['wpup_test']['calls']['wp_update_user'][0][0];
		$this->assertSame( 'ocean', $updated_user->admin_color );
		$this->assertSame( 'false', $updated_user->rich_editing );
		$this->assertSame( 'false', $updated_user->syntax_highlighting );
		$this->assertSame( 'false', $updated_user->infinite_scrolling );
		$this->assertSame( 'true', $updated_user->show_admin_bar_front );
		$this->assertSame( 'true', $updated_user->comment_shortcuts );
		$this->assertSame( 1, $updated_user->use_ssl );
	}

	public function test_options_use_legacy_core_defaults_when_modern_scheme_is_unavailable(): void {
		$section         = new WP_User_Profile_Options_Section();
		$section->id     = 'options';
		$section->errors = new WP_Error();

		$this->assertSame( 7, $section->save( new CoreProfileParityUser( 7 ) ) );

		$updated_user = $GLOBALS['wpup_test']['calls']['wp_update_user'][0][0];
		$this->assertSame( 'fresh', $updated_user->admin_color );
		$this->assertSame( 'true', $updated_user->rich_editing );
		$this->assertSame( 'true', $updated_user->syntax_highlighting );
		$this->assertSame( 'true', $updated_user->infinite_scrolling );
		$this->assertSame( 'false', $updated_user->show_admin_bar_front );
		$this->assertSame( '', $updated_user->comment_shortcuts );
		$this->assertSame( 0, $updated_user->use_ssl );
	}

	public function test_options_use_current_core_color_default_when_modern_scheme_is_available(): void {
		$GLOBALS['_wp_admin_css_colors']['modern'] = (object) array();

		$section         = new WP_User_Profile_Options_Section();
		$section->id     = 'options';
		$section->errors = new WP_Error();

		$this->assertSame( 7, $section->save( new CoreProfileParityUser( 7 ) ) );

		$updated_user = $GLOBALS['wpup_test']['calls']['wp_update_user'][0][0];
		$this->assertSame( 'modern', $updated_user->admin_color );
	}

	public function test_account_installs_selected_language_and_preserves_valid_email(): void {
		$GLOBALS['wpup_test']['capabilities']['install_languages'] = true;
		$GLOBALS['wpup_test']['can_install_language_pack']          = true;
		$GLOBALS['wpup_test']['language_pack_downloads']['fr_FR']   = true;
		$_POST = array(
			'email'  => 'person+profile@example.test',
			'locale' => 'fr_FR',
			'pass1'  => '',
			'pass2'  => '',
		);

		$user             = new CoreProfileParityUser( 7 );
		$user->user_login = 'person';
		$section          = new WP_User_Profile_Account_Section();
		$section->id      = 'account';
		$section->errors  = new WP_Error();

		$this->assertSame( 7, $section->save( $user ) );

		$updated_user = $GLOBALS['wpup_test']['calls']['wp_update_user'][0][0];
		$this->assertSame( 'person+profile@example.test', $updated_user->user_email );
		$this->assertSame( 'fr_FR', $updated_user->locale );
		$this->assertSame( array( array( 'fr_FR' ) ), $GLOBALS['wpup_test']['calls']['wp_download_language_pack'] );
	}

	public function test_account_trims_matching_passwords_like_core(): void {
		$_POST = array(
			'pass1' => '  secret phrase  ',
			'pass2' => '  secret phrase  ',
		);

		$user             = new CoreProfileParityUser( 7 );
		$user->user_login = 'person';
		$section          = new WP_User_Profile_Account_Section();
		$section->id      = 'account';
		$section->errors  = new WP_Error();

		$this->assertSame( 7, $section->save( $user ) );

		$updated_user = $GLOBALS['wpup_test']['calls']['wp_update_user'][0][0];
		$this->assertSame( 'secret phrase', $updated_user->user_pass );
	}
}
