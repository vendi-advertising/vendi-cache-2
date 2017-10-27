<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\XmlRpcMode;

class test_CacheBypasses_XmlRpcMode extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\XmlRpcMode::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstant::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstant::is_resource_not_cacheable_because_constant_is_true
     */
    public function test_is_resource_not_cacheable__XMLRPC_REQUEST__constant()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined('XmlRpcMode', 'XMLRPC_REQUEST');
    }

    /**
     * @covers \Vendi\Cache\CacheBypasses\XmlRpcMode::__construct
     * @covers \Vendi\Cache\CacheBypasses\XmlRpcMode::get_constant
     */
    public function test___construct()
    {
        $tester = new XmlRpcMode($this->__get_new_maestro(), 'XMLRPC_REQUEST');
        $this->assertSame('XMLRPC_REQUEST', $tester->get_constant());
    }
}
