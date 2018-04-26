<?php

declare(strict_types=1);

$_tests_dir = dirname(__DIR__) . '/vendor/WordPress/wordpress-develop/tests/phpunit/';

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Start up the WP testing environment.
require_once $_tests_dir . '/includes/bootstrap.php';

require_once dirname(dirname(__FILE__)) . '/vendi-cache.php';

require_once dirname(dirname(__FILE__)) . '/tests/includes/autoload.php';

//I don't get it. If the autoloader loads these then PHPUnit throws warnings.
require_once dirname(dirname(__FILE__)) . '/tests/includes/trait_test_logging.php';
require_once dirname(dirname(__FILE__)) . '/tests/includes/vendi_cache_test_base.php';
require_once dirname(dirname(__FILE__)) . '/tests/includes/vendi_cache_test_base_no_wordpress.php';
require_once dirname(dirname(__FILE__)) . '/tests/includes/cache_bypass_base.php';

// require dirname(dirname(__FILE__)) . '/tests/includes/nullhandler_log_handler.php';
// require dirname(dirname(__FILE__)) . '/tests/includes/file_system_for_tests.php';
// require dirname(dirname(__FILE__)) . '/tests/includes/non_global_constant_secretary.php';
