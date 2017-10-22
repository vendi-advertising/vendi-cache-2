<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\CacheBypasses\LoggedInUser;
use Vendi\Cache\Tests\cache_bypass_base;

class test_LoggedInUser extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\LoggedInUser::is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable()
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_secretary();

        $test = new LoggedInUser($maestro);

        $this->assertFalse($test->is_resource_not_cacheable());

        wp_set_current_user(0);
        $this->assertFalse($test->is_resource_not_cacheable());

        wp_set_current_user(1);
        $this->assertTrue($test->is_resource_not_cacheable());
    }
}
