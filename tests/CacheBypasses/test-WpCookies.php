<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\WpCookies;

class test_WpCookies extends cache_bypass_base
{
    /**
     * @covers Vendi\Cache\CacheBypasses\WpCookies::is_cacheable
     * @dataProvider provider_for_test_is_cacheable
     */
    public function test_is_cacheable( $name, $value, $is_cacheable )
    {
        //Common bootstrap
        $request = Request::create( '', 'GET', array(), array( $name => $value ) );
        $maestro = $this->__get_new_maestro( $request );

        $test = new WpCookies( $maestro );

        $this->assertSame( $is_cacheable, $test->is_cacheable() );
    }

    public function provider_for_test_is_cacheable( )
    {
        return [
                    //Unknown cookie, should cache
                    [ 'cheese',                 'cheese', true ],

                    //Should not cache
                    [ 'comment_author',         'cheese', false ],
                    [ 'wp-postpass',            'cheese', false ],
                    [ 'wf_logout',              'cheese', false ],
                    [ 'wordpress_logged_in',    'cheese', false ],
                    [ 'wptouch_switch_toggle',  'cheese', false ],
                    [ 'wpmp_switcher',          'cheese', false ],
            ];
    }
}
