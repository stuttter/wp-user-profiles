<?php

/**
 * Update every release-version declaration.
 */

declare(strict_types=1);

require_once __DIR__ . '/release-metadata.php';

$version = $argv[1] ?? '';
$root    = dirname(__DIR__);

if (! preg_match('/^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$/', $version)) {
	fwrite(STDERR, "Usage: composer release:version -- <major.minor.patch>\n");
	exit(2);
}

try {
	$updates = wup_release_version_updates($root, $version);
	wup_update_files_transactionally($updates);
} catch (RuntimeException $exception) {
	fwrite(STDERR, $exception->getMessage() . "\n");
	exit(1);
}

$errors = wup_release_metadata_errors($root);
if ($errors) {
	fwrite(STDERR, implode("\n", $errors) . "\n");
	exit(1);
}

fwrite(STDOUT, "Updated release metadata to {$version}. Regenerate the POT file before committing.\n");
