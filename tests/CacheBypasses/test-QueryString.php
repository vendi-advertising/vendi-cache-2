<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\QueryString;

class test_QueryString extends cache_bypass_base
{
    /**
     * @covers Vendi\Cache\CacheBypasses\QueryString::is_cacheable
     * @dataProvider provider_for_test_is_cacheable
     */
    public function test_is_cacheable( $url, $is_cacheable )
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro( Request::create( $url ) );

        $test = new QueryString( $maestro );

        $this->assertSame( $is_cacheable, $test->is_cacheable() );
    }

    public function provider_for_test_is_cacheable( )
    {
        return [
                    //Unknown cookie, should cache
                    [ 'http://www.example.com/cheese',     true ],

                    //Should not cache
                    [ 'http://www.example.com/cheese?1=2', false ],
            ];
    }
}
