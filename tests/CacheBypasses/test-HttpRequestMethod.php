<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\HttpRequestMethod;

class test_HttpRequestMethod extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\HttpRequestMethod::is_resource_not_cacheable
     * @dataProvider provider_for_test_is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable( $name, $is_resource_not_cacheable )
    {
        $maestro = $this->__get_new_maestro(
                                                Request::create( '', $name )
                                        );

        $test = new HttpRequestMethod( $maestro );
        $this->assertSame( $is_resource_not_cacheable, $test->is_resource_not_cacheable() );
    }

    public function provider_for_test_is_resource_not_cacheable()
    {
        $legacy_cache_constants = [
                                    [ 'GET',    false ],
                                    [ 'HEAD',   true ],
                                    [ 'POST',   true ],
                                    [ 'CHEESE', true ],
                                 ];

        return $legacy_cache_constants;
    }

}
