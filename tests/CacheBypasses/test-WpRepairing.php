<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\WpRepairing;

class test_CacheBypasses_WpRepairing extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\WpRepairing::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstant::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstant::is_resource_not_cacheable_because_constant_is_true
     */
    public function test_is_resource_not_cacheable__WP_REPAIRING__constant()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined('WpRepairing', 'WP_REPAIRING');
    }

    /**
     * @covers \Vendi\Cache\CacheBypasses\WpRepairing::__construct
     * @covers \Vendi\Cache\CacheBypasses\WpRepairing::get_constant
     */
    public function test___construct()
    {
        $tester = new WpRepairing($this->__get_new_maestro(), 'WP_REPAIRING');
        $this->assertSame('WP_REPAIRING', $tester->get_constant());
    }
}
