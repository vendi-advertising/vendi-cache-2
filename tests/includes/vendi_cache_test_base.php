<?php

declare(strict_types=1);

namespace Vendi\Cache\Tests;

/**
 * @coversNothing
 */
class vendi_cache_test_base extends \WP_UnitTestCase implements vendi_cache_test_base_interface
{
    use trait_test_logging;
}
