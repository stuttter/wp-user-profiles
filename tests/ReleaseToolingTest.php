<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/bin/release-metadata.php';

final class ReleaseToolingTest extends TestCase {
	private $temporary_directory;

	protected function setUp(): void {
		$this->temporary_directory = sys_get_temp_dir() . '/wup-release-' . bin2hex(random_bytes(8));
		mkdir($this->temporary_directory, 0700, true);

		$root = dirname(__DIR__);
		foreach (array('wp-user-profiles.php', 'readme.txt', 'package.json', 'package-lock.json') as $file) {
			copy($root . '/' . $file, $this->temporary_directory . '/' . $file);
		}
	}

	protected function tearDown(): void {
		foreach (glob($this->temporary_directory . '/*') ?: array() as $file) {
			unlink($file);
		}
		rmdir($this->temporary_directory);
	}

	public function test_current_release_metadata_agrees(): void {
		$this->assertSame(array(), wup_release_metadata_errors($this->temporary_directory));
	}

	public function test_readme_changelog_contains_current_release(): void {
		$version = wup_read_header($this->temporary_directory . '/wp-user-profiles.php', 'Version');
		$readme  = (string) file_get_contents($this->temporary_directory . '/readme.txt');

		$this->assertMatchesRegularExpression(
			'/^=\s*\[?' . preg_quote($version, '/') . '\]?(?=\s*(?:-|=|$))/mi',
			$readme
		);
	}

	public function test_version_drift_identifies_its_location(): void {
		$readme         = $this->temporary_directory . '/readme.txt';
		$current_version = wup_read_header($readme, 'Stable tag');
		$drift_version   = '0.0.0' === $current_version ? '0.0.1' : '0.0.0';

		wup_update_header($readme, 'Stable tag', $drift_version);

		$this->assertSame(
			array("Version drift in readme.txt Stable tag: {$drift_version}; expected {$current_version}."),
			wup_release_metadata_errors($this->temporary_directory)
		);
	}

	public function test_header_updater_replaces_only_the_requested_header(): void {
		$plugin = $this->temporary_directory . '/wp-user-profiles.php';
		wup_update_header($plugin, 'Version', '2.7.0');

		$this->assertSame('2.7.0', wup_read_header($plugin, 'Version'));
		$this->assertSame('7.4', wup_read_header($plugin, 'Requires PHP'));
	}

	public function test_release_version_updater_changes_every_declaration(): void {
		$current_version = wup_read_header($this->temporary_directory . '/wp-user-profiles.php', 'Version');
		$new_version     = '99.0.0' === $current_version ? '99.0.1' : '99.0.0';

		wup_update_files_transactionally(
			wup_release_version_updates($this->temporary_directory, $new_version)
		);

		$this->assertSame(array(), wup_release_metadata_errors($this->temporary_directory));
		foreach (wup_release_versions($this->temporary_directory) as $version) {
			$this->assertSame($new_version, $version);
		}
	}

	public function test_release_version_updater_rolls_back_every_file_after_a_write_failure(): void {
		$current_version = wup_read_header($this->temporary_directory . '/wp-user-profiles.php', 'Version');
		$new_version     = '99.0.0' === $current_version ? '99.0.1' : '99.0.0';
		$updates         = wup_release_version_updates($this->temporary_directory, $new_version);
		$originals       = array();

		foreach ($updates as $path => $contents) {
			$originals[$path] = (string) file_get_contents($path);
		}

		$write_count = 0;
		$writer      = static function (string $path, string $contents) use (&$write_count): void {
			++$write_count;
			if (2 === $write_count) {
				throw new RuntimeException('Injected write failure.');
			}
			wup_atomic_write($path, $contents);
		};

		try {
			wup_update_files_transactionally($updates, $writer);
			$this->fail('Expected the injected write failure.');
		} catch (RuntimeException $exception) {
			$this->assertStringContainsString('original files were restored', $exception->getMessage());
		}

		foreach ($originals as $path => $contents) {
			$this->assertSame($contents, file_get_contents($path));
		}
		$this->assertSame(array(), wup_release_metadata_errors($this->temporary_directory));
	}
}
