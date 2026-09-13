<?php

/**
 * Shared release-metadata helpers.
 */

declare(strict_types=1);

/**
 * Read and decode a JSON object.
 *
 * @return array<string, mixed>
 */
function wup_read_json(string $path): array {
	if (! is_file($path)) {
		throw new RuntimeException("Missing required file: {$path}");
	}

	try {
		$value = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
	} catch (JsonException $exception) {
		throw new RuntimeException("Invalid JSON in {$path}: {$exception->getMessage()}");
	}

	if (! is_array($value)) {
		throw new RuntimeException("Expected a JSON object in {$path}.");
	}

	return $value;
}

/**
 * Extract a metadata header from a text file.
 */
function wup_read_header(string $path, string $header): string {
	if (! is_file($path)) {
		throw new RuntimeException("Missing required file: {$path}");
	}

	$contents = (string) file_get_contents($path);
	if (! preg_match('/^[ \t*#@]*' . preg_quote($header, '/') . ':\s*(.+)$/mi', $contents, $match)) {
		throw new RuntimeException("Missing {$header} header in {$path}.");
	}

	return trim($match[1]);
}

/**
 * Return every release-version declaration.
 *
 * @return array<string, string>
 */
function wup_release_versions(string $root): array {
	$package      = wup_read_json($root . '/package.json');
	$package_lock = wup_read_json($root . '/package-lock.json');

	return array(
		'plugin header'          => wup_read_header($root . '/wp-user-profiles.php', 'Version'),
		'readme.txt Stable tag'  => wup_read_header($root . '/readme.txt', 'Stable tag'),
		'package.json'           => (string) ($package['version'] ?? ''),
		'package-lock.json'      => (string) ($package_lock['version'] ?? ''),
		'package-lock root entry' => (string) ($package_lock['packages']['']['version'] ?? ''),
	);
}

/**
 * Validate all release-version declarations.
 *
 * @return list<string>
 */
function wup_release_metadata_errors(string $root): array {
	try {
		$versions = wup_release_versions($root);
	} catch (RuntimeException $exception) {
		return array($exception->getMessage());
	}

	$errors  = array();
	$version = reset($versions);

	if (! is_string($version) || ! preg_match('/^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$/', $version)) {
		$errors[] = "Plugin version is not an exact semantic version: {$version}";
	}

	foreach ($versions as $location => $candidate) {
		if ('' === $candidate) {
			$errors[] = "Missing version in {$location}.";
		} elseif ($candidate !== $version) {
			$errors[] = "Version drift in {$location}: {$candidate}; expected {$version}.";
		}
	}

	return $errors;
}

/**
 * Replace exactly one metadata header.
 */
function wup_update_header(string $path, string $header, string $version): void {
	$contents = (string) file_get_contents($path);
	$updated  = wup_update_header_contents($contents, $path, $header, $version);

	if (false === file_put_contents($path, $updated)) {
		throw new RuntimeException("Unable to write {$path}.");
	}
}

/**
 * Replace exactly one metadata header in supplied contents.
 */
function wup_update_header_contents(string $contents, string $path, string $header, string $version): string {
	$updated = preg_replace(
		'/^([ \t*#@]*' . preg_quote($header, '/') . ':\s*).+$/mi',
		'${1}' . $version,
		$contents,
		1,
		$count
	);

	if (1 !== $count || null === $updated) {
		throw new RuntimeException("Unable to update {$header} in {$path}.");
	}

	return $updated;
}

/**
 * Build the complete set of release-version file updates in memory.
 *
 * @return array<string, string>
 */
function wup_release_version_updates(string $root, string $version): array {
	$errors = wup_release_metadata_errors($root);
	if ($errors) {
		throw new RuntimeException("Release metadata is already inconsistent:\n" . implode("\n", $errors));
	}

	$versions        = wup_release_versions($root);
	$current_version = reset($versions);
	$plugin_path     = $root . '/wp-user-profiles.php';
	$readme_path     = $root . '/readme.txt';
	$package_path    = $root . '/package.json';
	$lock_path       = $root . '/package-lock.json';

	$updates = array(
		$plugin_path  => wup_update_header_contents((string) file_get_contents($plugin_path), $plugin_path, 'Version', $version),
		$readme_path  => wup_update_header_contents((string) file_get_contents($readme_path), $readme_path, 'Stable tag', $version),
		$package_path => wup_update_json_version_contents((string) file_get_contents($package_path), $package_path, (string) $current_version, $version, 1),
		$lock_path    => wup_update_json_version_contents((string) file_get_contents($lock_path), $lock_path, (string) $current_version, $version, 2),
	);

	$package      = json_decode($updates[$package_path], true);
	$package_lock = json_decode($updates[$lock_path], true);
	if (
		! is_array($package)
		|| ! is_array($package_lock)
		|| $version !== ($package['version'] ?? null)
		|| $version !== ($package_lock['version'] ?? null)
		|| $version !== ($package_lock['packages']['']['version'] ?? null)
	) {
		throw new RuntimeException('Generated package metadata did not contain the requested version.');
	}

	return $updates;
}

/**
 * Replace an exact number of JSON version declarations.
 */
function wup_update_json_version_contents(
	string $contents,
	string $path,
	string $current_version,
	string $version,
	int $expected_count
): string {
	$updated = preg_replace(
		'/^([ \t]*"version":\s*)"' . preg_quote($current_version, '/') . '"(,?)$/m',
		'${1}"' . $version . '"${2}',
		$contents,
		$expected_count,
		$count
	);

	if ($expected_count !== $count || null === $updated) {
		throw new RuntimeException("Unable to update every release version in {$path}.");
	}

	return $updated;
}

/**
 * Write one file atomically.
 */
function wup_atomic_write(string $path, string $contents): void {
	$temporary_path = tempnam(dirname($path), '.wup-release-');
	if (false === $temporary_path) {
		throw new RuntimeException("Unable to stage {$path}.");
	}

	try {
		if (false === file_put_contents($temporary_path, $contents)) {
			throw new RuntimeException("Unable to stage {$path}.");
		}

		$permissions = fileperms($path);
		if (false !== $permissions) {
			chmod($temporary_path, $permissions & 0777);
		}

		if (! rename($temporary_path, $path)) {
			throw new RuntimeException("Unable to replace {$path}.");
		}
	} finally {
		if (is_file($temporary_path)) {
			unlink($temporary_path);
		}
	}
}

/**
 * Update a set of files and restore every original if any write fails.
 *
 * @param array<string, string> $updates Updated contents keyed by absolute path.
 */
function wup_update_files_transactionally(array $updates, ?callable $writer = null): void {
	$originals = array();
	foreach ($updates as $path => $contents) {
		$original = file_get_contents($path);
		if (false === $original) {
			throw new RuntimeException("Unable to read {$path} before updating it.");
		}
		$originals[$path] = $original;
	}

	$writer = $writer ?? 'wup_atomic_write';

	try {
		foreach ($updates as $path => $contents) {
			$writer($path, $contents);
		}
	} catch (Throwable $exception) {
		$rollback_errors = array();
		foreach ($originals as $path => $contents) {
			try {
				wup_atomic_write($path, $contents);
			} catch (Throwable $rollback_exception) {
				$rollback_errors[] = $rollback_exception->getMessage();
			}
		}

		$message = 'Release metadata update failed; original files were restored.';
		if ($rollback_errors) {
			$message .= " Rollback also failed:\n" . implode("\n", $rollback_errors);
	}

		throw new RuntimeException($message, 0, $exception);
	}
}
