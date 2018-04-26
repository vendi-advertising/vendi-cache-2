<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheExclusions\Comparators\MatchesExactly;
use Vendi\Cache\CacheExclusions\Sources\UrlSource;

/**
 * @group CacheExclusions
 */
class test_UrlSource extends vendi_cache_test_base_no_wordpress
{
    /**
     * @covers \Vendi\Cache\CacheExclusions\Sources\UrlSource::get_storage_name
     */
    public function test__get_storage_name()
    {
        $this->assertSame('url', (new UrlSource($this->__get_new_maestro()))->get_storage_name());
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Sources\UrlSource::should_request_be_excluded_from_caching
     * @covers \Vendi\Cache\CacheExclusions\Sources\UrlSource::get_url_path
     * @covers \Vendi\Cache\CacheExclusions\Sources\UrlSource::__construct
     * @covers \Vendi\Cache\CacheExclusions\Sources\UrlSource::get_maestro
     * @dataProvider provider_for__test__should_request_be_excluded_from_caching
     * @param mixed $expected
     * @param mixed $class_name_base
     * @param mixed $url
     * @param mixed $string_to_test
     */
    public function test__should_request_be_excluded_from_caching($expected, $class_name_base, $url, $string_to_test)
    {
        $root_namespace       = 'Vendi\Cache';
        $exclusion_namespace  = "$root_namespace\\CacheExclusions";
        $comparator_namespace = "$exclusion_namespace\\Comparators";
        $class_name_full      = "$comparator_namespace\\$class_name_base";

        $request = $this->__create_server_request_from_url($url);
        $source = new UrlSource($this->__get_new_maestro($request));

        $this->assertInstanceOf('Vendi\Cache\Maestro', $source->get_maestro());

        $comparator = new $class_name_full();
        $this->assertSame($expected, $source->should_request_be_excluded_from_caching($comparator, $string_to_test));
    }

    public function provider_for__test__should_request_be_excluded_from_caching()
    {
        return [
                    [ true,  'MatchesExactly', 'http://www.example.net/cheese', '/cheese' ],
                    [ false, 'MatchesExactly', 'http://www.example.net/chees',  '/cheese' ],
                    [ false, 'MatchesExactly', 'http://www.example.net/cheese', '/chees' ],

                    [ true,  'Contains',       'http://www.example.net/cheese', '/cheese' ],
                    [ true,  'Contains',       'http://www.example.net/cheese', 'che' ],
                    [ true,  'Contains',       'http://www.example.net/cheese', '/che' ],
                    [ false, 'Contains',       'http://www.example.net/cheese', 'chs' ],

                    [ true,  'StartsWith',     'http://www.example.net/cheese', '/cheese' ],
                    [ true,  'StartsWith',     'http://www.example.net/cheese', '/chee' ],
                    [ true,  'StartsWith',     'http://www.example.net/cheese', '/' ],
                    [ false, 'StartsWith',     'http://www.example.net/cheese', 'cheese' ],

                    [ true,  'EndsWith',       'http://www.example.net/cheese', '/cheese' ],
                    [ true,  'EndsWith',       'http://www.example.net/cheese', 'ese' ],
                    [ false, 'EndsWith',       'http://www.example.net/cheese', '/chees' ],

                    //PSR-7 allows the path to not start with a slash however we do. These two
                    //are considered equal from the caching perspective
                    [ true,  'MatchesExactly', 'http://www.example.net/', '/' ],
                    [ true,  'MatchesExactly', 'http://www.example.net', '/' ],

        ];
    }
}
