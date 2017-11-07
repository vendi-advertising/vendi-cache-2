<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\ShortInit;

class test_CacheBypasses_ShortInit extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\ShortInit::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstant::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstant::is_resource_not_cacheable_because_constant_is_true
     */
    public function test_is_resource_not_cacheable__SHORTINIT__constant()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined('ShortInit', 'SHORTINIT');
    }

    /**
     * @covers \Vendi\Cache\CacheBypasses\ShortInit::__construct
     * @covers \Vendi\Cache\CacheBypasses\ShortInit::get_constant
     */
    public function test___construct()
    {
        $tester = new ShortInit($this->__get_new_maestro(), 'SHORTINIT');
        $this->assertSame('SHORTINIT', $tester->get_constant());
    }
}
