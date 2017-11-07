<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\WpInstallingNetwork;

class test_CacheBypasses_WpInstallingNetwork extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\WpInstallingNetwork::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstant::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstant::is_resource_not_cacheable_because_constant_is_true
     */
    public function test_is_resource_not_cacheable__WP_INSTALLING_NETWORK__constant()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined('WpInstallingNetwork', 'WP_INSTALLING_NETWORK');
    }

    /**
     * @covers \Vendi\Cache\CacheBypasses\WpInstallingNetwork::__construct
     * @covers \Vendi\Cache\CacheBypasses\WpInstallingNetwork::get_constant
     */
    public function test___construct()
    {
        $tester = new WpInstallingNetwork($this->__get_new_maestro(), 'WP_INSTALLING_NETWORK');
        $this->assertSame('WP_INSTALLING_NETWORK', $tester->get_constant());
    }
}
