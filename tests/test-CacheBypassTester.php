<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypassTester;

class test_CacheBypassTester extends vendi_cache_test_base_no_wordpress
{
    /**
     * @covers \Vendi\Cache\CacheBypassTester::is_resource_not_cacheable
     */
    public function test_test_request()
    {
        $tester = new CacheBypassTester($this->__get_new_maestro());
        $this->assertInternalType('bool', $tester->is_resource_not_cacheable());
    }

    /**
     * @covers \Vendi\Cache\CacheBypassTester::__construct
     * @covers \Vendi\Cache\CacheBypassTester::get_maestro
     */
    public function test__various_methods()
    {
        $tester = new CacheBypassTester($this->__get_new_maestro());
        $this->assertInstanceOf('Vendi\Cache\Maestro', $tester->get_maestro());
    }
}
