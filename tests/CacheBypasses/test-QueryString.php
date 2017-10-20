<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\QueryString;

class test_QueryString extends cache_bypass_base
{
    /**
     * @covers Vendi\Cache\CacheBypasses\QueryString::is_resource_not_cacheable
     * @dataProvider provider_for_test_is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable( $url, $is_resource_not_cacheable )
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro( $this->__create_server_request_from_url( $url ) );

        $test = new QueryString( $maestro );

        $this->assertSame( $is_resource_not_cacheable, $test->is_resource_not_cacheable() );
    }

    public function provider_for_test_is_resource_not_cacheable( )
    {
        return [
                    //Unknown cookie, should cache
                    [ 'http://www.example.com/cheese',     false ],

                    //Should not cache
                    [ 'http://www.example.com/cheese?1=2', true ],
            ];
    }
}
