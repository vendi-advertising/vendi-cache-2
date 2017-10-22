<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\CacheBypasses\WpCorePage;
use Vendi\Cache\Tests\cache_bypass_base;

class test_WpCorePage extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\WpCorePage::is_resource_not_cacheable
     * @dataProvider provider_for_test_is_resource_not_cacheable
     * @param mixed $url
     * @param mixed $is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable($url, $is_resource_not_cacheable)
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro($this->__create_server_request_from_url($url));

        $test = new WpCorePage($maestro);

        $this->assertSame($is_resource_not_cacheable, $test->is_resource_not_cacheable());
    }

    public function provider_for_test_is_resource_not_cacheable()
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
