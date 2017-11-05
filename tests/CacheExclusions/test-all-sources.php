<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheExclusions\Comparators\MatchesExactly;
use Vendi\Cache\CacheExclusions\Sources\UrlSource;

class test_all_sources extends vendi_cache_test_base
{
    /**
     * @covers \Vendi\Cache\CacheExclusions\Sources\UrlSource::__construct
     * @covers \Vendi\Cache\CacheExclusions\Sources\UrlSource::get_maestro
     */
    public function test_url()
    {
        $request = $this->__create_server_request_from_url('http://www.example.net/cheese');

        $url_source = new UrlSource($this->__get_new_maestro($request));
        $this->assertInstanceOf('Vendi\Cache\Maestro', $url_source->get_maestro());
        $this->assertTrue($url_source->should_request_be_excluded_from_caching(new MatchesExactly(), 'http://www.example.net/cheese'));

        // $possible_sources = [
        //                         'url'               => 'UrlSource',
        //                         'user-agent'        => 'UserAgentSource',
        //                         'cookie-name'       => 'CookieNameSource',
        // ];

        // $possible_comparators = [
        //                         'matches-exactly'   => 'MatchesExactly',
        //                         'contains'          => 'Contains',
        //                         'ends-with'         => 'EndsWith',
        //                         'starts-with'       => 'StartsWith',
        // ];

        // $root_namespace       = 'Vendi\Cache';
        // $exclusion_namespace  = "$root_namespace\\CacheExclusions";
        // $source_namespace     = "$exclusion_namespace\\Sources";
        // $comparator_namespace = "$exclusion_namespace\\Comparators";

        // foreach ($possible_sources as $source_key => $souce_class_name) {
        //     $class_name = "$source_namespace\\$souce_class_name";

        //     $obj = new $class_name($this->__get_new_maestro());

        //     $this->assertSame($source_key, $obj->get_storage_name());
        //     $this->assertSame('cheese', $obj->get_value());
        // }
    }
}
