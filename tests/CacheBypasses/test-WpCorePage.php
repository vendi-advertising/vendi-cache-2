<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\WpCorePage;

class test_WpCorePage extends cache_bypass_base
{
    /**
     * @covers Vendi\Cache\CacheBypasses\WpCorePage::is_cacheable
     * @dataProvider provider_for_test_is_cacheable
     */
    public function test_is_cacheable( $url, $is_cacheable )
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro( Request::create( $url ) );

        $test = new WpCorePage( $maestro );

        $this->assertSame( $is_cacheable, $test->is_cacheable() );
    }

    public function provider_for_test_is_cacheable( )
    {
        return [
                    //Unknown cookie, should cache
                    [ 'http://www.example.com/cheese',                  true ],

                    //Should not cache
                    [ 'http://www.example.com/wp-login.php',            false ],
                    [ 'http://www.example.com/wp-signup.php',           false ],
                    [ 'http://www.example.com/wp-trackback.php',        false ],
                    [ 'http://www.example.com/xmlrpc.php',              false ],
            ];
    }
}
