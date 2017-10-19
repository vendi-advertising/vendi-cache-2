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

        wp_set_current_user( 0 );
        $this->assertTrue( $test->is_cacheable() );

        wp_set_current_user( 1 );
        $this->assertFalse( $test->is_cacheable() );
    }
}
