<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheExclusions\Comparators\MatchesExactly;
use Vendi\Cache\CacheExclusions\Sources\CookieNameSource;

/**
 * @group CacheExclusions
 */
class test_CookieNameSource extends vendi_cache_test_base
{
    /**
     * @covers \Vendi\Cache\CacheExclusions\Sources\CookieNameSource::get_storage_name
     */
    public function test__get_storage_name()
    {
        $this->assertSame('cookie-name', (new CookieNameSource($this->__get_new_maestro()))->get_storage_name());
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Sources\CookieNameSource::should_request_be_excluded_from_caching
     */
    public function test__should_request_be_excluded_from_caching__no_cookies()
    {
        $request = $this->__create_server_request_from_url('', 'GET', []);
        $source = new CookieNameSource($this->__get_new_maestro($request));

        $this->assertInstanceOf('Vendi\Cache\Maestro', $source->get_maestro());

        $comparator = new \Vendi\Cache\CacheExclusions\Comparators\MatchesExactly();
        $this->assertFalse($source->should_request_be_excluded_from_caching($comparator, 'CHEESE'));
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Sources\CookieNameSource::should_request_be_excluded_from_caching
     * @covers \Vendi\Cache\CacheExclusions\Sources\CookieNameSource::get_cookies
     * @covers \Vendi\Cache\CacheExclusions\Sources\CookieNameSource::__construct
     * @covers \Vendi\Cache\CacheExclusions\Sources\CookieNameSource::get_maestro
     * @dataProvider provider_for__test__should_request_be_excluded_from_caching
     */
    public function test__should_request_be_excluded_from_caching($expected, $class_name_base, $cookie_name, $string_to_test)
    {
        $root_namespace       = 'Vendi\Cache';
        $exclusion_namespace  = "$root_namespace\\CacheExclusions";
        $comparator_namespace = "$exclusion_namespace\\Comparators";
        $class_name_full      = "$comparator_namespace\\$class_name_base";

        $request = $this->__create_server_request_from_url('', 'GET', [ $cookie_name => 'CHEESE' ]);
        $source = new CookieNameSource($this->__get_new_maestro($request));

        $this->assertInstanceOf('Vendi\Cache\Maestro', $source->get_maestro());

        $comparator = new $class_name_full();
        $this->assertSame($expected, $source->should_request_be_excluded_from_caching($comparator, $string_to_test));
    }

    public function provider_for__test__should_request_be_excluded_from_caching()
    {
        return [
                    [ true,  'MatchesExactly', 'WP_LOGIN', 'WP_LOGIN' ],
                    [ false, 'MatchesExactly', 'WP_LOGIN', 'WP__LOGIN' ],

                    [ true,  'Contains',       'WP_LOGIN', 'LOG' ],
                    [ true,  'Contains',       'WP_LOGIN', 'WP_LOGIN' ],
                    [ false, 'Contains',       'WP_LOGIN', 'WPLOGIN' ],

                    [ true,  'StartsWith',     'WP_LOGIN', 'WP_LOG' ],
                    [ true,  'StartsWith',     'WP_LOGIN', 'WP_LOGIN' ],
                    [ false, 'StartsWith',     'WP_LOGIN', 'LOGIN' ],

                    [ true,  'EndsWith',       'WP_LOGIN', 'LOGIN' ],
                    [ true,  'EndsWith',       'WP_LOGIN', 'WP_LOGIN' ],
                    [ false, 'EndsWith',       'WP_LOGIN', 'WPLOGIN' ],
        ];
    }
}
