<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\HttpRequestMethod;

class test_HttpRequestMethod extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\HttpRequestMethod::is_cacheable
     * @dataProvider provider_for_test_is_cacheable
     */
    public function test_is_cacheable( $name, $is_cacheable )
    {
        $maestro = $this->__get_new_maestro(
                                                Request::create( '', $name )
                                        );

        $test = new HttpRequestMethod( $maestro );
        $this->assertSame( $is_cacheable, $test->is_cacheable() );
    }

    public function provider_for_test_is_cacheable()
    {
        $legacy_cache_constants = [
                                    [ 'GET',    true ],
                                    [ 'HEAD',   false ],
                                    [ 'POST',   false ],
                                    [ 'CHEESE', false ],
                                 ];

        return $legacy_cache_constants;
    }

}
