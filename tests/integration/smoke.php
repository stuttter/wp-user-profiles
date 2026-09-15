<?php

defined( 'ABSPATH' ) || exit( 1 );

$smoke_test = is_multisite()
	? __DIR__ . '/multisite-smoke.php'
	: __DIR__ . '/single-site-smoke.php';

require $smoke_test;
