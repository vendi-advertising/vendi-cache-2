<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\WpCookies;

class test_WpCookies extends cache_bypass_base
{
    /**
     * @covers Vendi\Cache\CacheBypasses\WpCookies::is_resource_not_cacheable
     * @dataProvider provider_for_test_is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable( $name, $value, $is_resource_not_cacheable )
    {
        //Common bootstrap
        $request = $this->__create_server_request_from_url( '', 'GET', array( $name => $value ) );
        $maestro = $this->__get_new_maestro( $request );

        $test = new WpCookies( $maestro );

        $this->assertSame( $is_resource_not_cacheable, $test->is_resource_not_cacheable() );
    }

    public function provider_for_test_is_resource_not_cacheable( )
    {
        return [
                    //Unknown cookie, should cache
                    [ 'cheese',                 'cheese', false ],

                    //Should not cache
                    [ 'comment_author',         'cheese', true ],
                    [ 'wp-postpass',            'cheese', true ],
                    [ 'wf_logout',              'cheese', true ],
                    [ 'wordpress_logged_in',    'cheese', true ],
                    [ 'wptouch_switch_toggle',  'cheese', true ],
                    [ 'wpmp_switcher',          'cheese', true ],
            ];
    }
}
