<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\CacheBypasses\PhpError;
use Vendi\Cache\Tests\cache_bypass_base;

class test_PhpError extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\PhpError::is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable()
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_secretary();
        $test = new PhpError($maestro);
        $this->assertFalse($test->is_resource_not_cacheable());

        $cache_settings->set_constant('VENDI_CACHE_PHP_ERROR', true);
        $this->assertTrue($test->is_resource_not_cacheable());
    }
}
