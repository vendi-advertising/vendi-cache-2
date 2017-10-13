<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\LoggedInUser;

class test_LoggedInUser extends cache_bypass_base
{
    /**
     * @covers Vendi\Cache\CacheBypasses\LoggedInUser::is_cacheable
     */
    public function test_is_cacheable( )
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_secretary();

        $test = new LoggedInUser( $maestro );

        $this->assertTrue( $test->is_cacheable() );

        $cache_settings->set_function(
                                        'wp_get_current_user',
                                        function()
                                        {
                                            return new \WP_User( 0 );
                                        }
            );

        $this->assertTrue( $test->is_cacheable() );

        $cache_settings->set_function(
                                        'wp_get_current_user',
                                        function()
                                        {
                                            return new \WP_User( 1 );
                                        }
            );

        $this->assertFalse( $test->is_cacheable() );

    }
}
