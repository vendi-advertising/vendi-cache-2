<?php

$_tests_dir = dirname( __DIR__ ) . '/vendor/WordPress/wordpress-develop/tests/phpunit/';

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Start up the WP testing environment.
require_once $_tests_dir . '/includes/bootstrap.php';

require dirname( dirname( __FILE__ ) ) . '/vendi-cache.php';
