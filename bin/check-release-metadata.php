<?php

/**
 * Check release-version declarations for drift.
 */

declare(strict_types=1);

require_once __DIR__ . '/release-metadata.php';

$root   = dirname(__DIR__);
$errors = wup_release_metadata_errors($root);

if ($errors) {
	fwrite(STDERR, implode("\n", $errors) . "\n");
	exit(1);
}

$versions = wup_release_versions($root);
fwrite(STDOUT, 'Release metadata agrees on version ' . reset($versions) . ".\n");
