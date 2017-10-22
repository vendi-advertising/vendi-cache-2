<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\CacheBypasses\HttpRequestMethod;
use Vendi\Cache\Tests\cache_bypass_base;

class test_HttpRequestMethod extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\HttpRequestMethod::is_resource_not_cacheable
     * @dataProvider provider_for_test_is_resource_not_cacheable
     * @param mixed $name
     * @param mixed $is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable($name, $is_resource_not_cacheable)
    {
        $maestro = $this->__get_new_maestro(
                                                $this->__create_server_request_from_url('', $name)
                                        );

        $test = new HttpRequestMethod($maestro);
        $this->assertSame($is_resource_not_cacheable, $test->is_resource_not_cacheable());
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
