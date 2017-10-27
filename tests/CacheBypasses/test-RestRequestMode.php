<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\RestRequestMode;

class test_CacheBypasses_RestRequestMode extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\RestRequestMode::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstant::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstant::is_resource_not_cacheable_because_constant_is_true
     */
    public function test_is_resource_not_cacheable__REST_REQUEST__constant()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined('RestRequestMode', 'REST_REQUEST');
    }

    /**
     * @covers \Vendi\Cache\CacheBypasses\RestRequestMode::__construct
     * @covers \Vendi\Cache\CacheBypasses\RestRequestMode::get_constant
     */
    public function test___construct()
    {
        $tester = new RestRequestMode($this->__get_new_maestro(), 'REST_REQUEST');
        $this->assertSame('REST_REQUEST', $tester->get_constant());
    }
}
