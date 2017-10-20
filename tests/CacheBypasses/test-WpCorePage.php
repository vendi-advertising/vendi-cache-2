<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\WpCorePage;

class test_WpCorePage extends cache_bypass_base
{
    /**
     * @covers Vendi\Cache\CacheBypasses\WpCorePage::is_resource_not_cacheable
     * @dataProvider provider_for_test_is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable( $url, $is_resource_not_cacheable )
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro( Request::create( $url ) );

        $test = new WpCorePage( $maestro );

        $this->assertSame( $is_resource_not_cacheable, $test->is_resource_not_cacheable() );
    }

    public function provider_for_test_is_resource_not_cacheable( )
    {
        return [
                    //Unknown cookie, should cache
                    [ 'http://www.example.com/cheese',                  false ],

                    //Should not cache
                    [ 'http://www.example.com/wp-login.php',            true ],
                    [ 'http://www.example.com/wp-signup.php',           true ],
                    [ 'http://www.example.com/wp-trackback.php',        true ],
                    [ 'http://www.example.com/xmlrpc.php',              true ],
            ];
    }
}
