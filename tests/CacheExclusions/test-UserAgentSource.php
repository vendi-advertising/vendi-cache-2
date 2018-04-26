<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheExclusions\Comparators\MatchesExactly;
use Vendi\Cache\CacheExclusions\Sources\UserAgentSource;

/**
 * @group CacheExclusions
 */
class test_UserAgentSource extends vendi_cache_test_base
{
    /**
     * @covers \Vendi\Cache\CacheExclusions\Sources\UserAgentSource::get_storage_name
     */
    public function test__get_storage_name()
    {
        $this->assertSame('user-agent', (new UserAgentSource($this->__get_new_maestro()))->get_storage_name());
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Sources\UserAgentSource::should_request_be_excluded_from_caching
     * @covers \Vendi\Cache\CacheExclusions\Sources\UserAgentSource::get_user_agent
     */
    public function test__weird_user_agents()
    {
        //Create a request with two user agent headers
        $request = $this->__create_server_request_with_custom_headers([]);

        $this->assertNull((new UserAgentSource($this->__get_new_maestro($request)))->get_user_agent());
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Sources\UserAgentSource::should_request_be_excluded_from_caching
     */
    public function test__should_request_be_excluded_from_caching__null_ua()
    {
        $request = $this->__create_server_request_with_custom_headers(['HTTP_USER_AGENT' => '']);

        $source = new UserAgentSource($this->__get_new_maestro($request));

        $this->assertInstanceOf('Vendi\Cache\Maestro', $source->get_maestro());

        $comparator = new \Vendi\Cache\CacheExclusions\Comparators\MatchesExactly();
        $this->assertFalse($source->should_request_be_excluded_from_caching($comparator, 'Doesn\'t Matter'));
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Sources\UserAgentSource::should_request_be_excluded_from_caching
     * @covers \Vendi\Cache\CacheExclusions\Sources\UserAgentSource::get_user_agent
     * @covers \Vendi\Cache\CacheExclusions\Sources\UserAgentSource::__construct
     * @covers \Vendi\Cache\CacheExclusions\Sources\UserAgentSource::get_maestro
     * @dataProvider provider_for__test__should_request_be_excluded_from_caching
     * @param mixed $expected
     * @param mixed $class_name_base
     * @param mixed $user_agent
     * @param mixed $string_to_test
     */
    public function test__should_request_be_excluded_from_caching($expected, $class_name_base, $user_agent, $string_to_test)
    {
        $root_namespace       = 'Vendi\Cache';
        $exclusion_namespace  = "$root_namespace\\CacheExclusions";
        $comparator_namespace = "$exclusion_namespace\\Comparators";
        $class_name_full      = "$comparator_namespace\\$class_name_base";

        $request = $this->__create_server_request_with_custom_headers(['HTTP_USER_AGENT' => $user_agent]);

        $source = new UserAgentSource($this->__get_new_maestro($request));

        $this->assertInstanceOf('Vendi\Cache\Maestro', $source->get_maestro());

        $comparator = new $class_name_full();
        $this->assertSame($expected, $source->should_request_be_excluded_from_caching($comparator, $string_to_test));
    }

    public function provider_for__test__should_request_be_excluded_from_caching()
    {
        return [
                    [ true,  'MatchesExactly', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0' ],
                    [ false, 'MatchesExactly', 'Mozilla/6.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0' ],

                    [ true,  'Contains',       'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Mozilla' ],
                    [ false, 'Contains',       'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Chrome' ],

                    [ true,  'StartsWith',     'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Mozilla' ],
                    [ false, 'StartsWith',     'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox' ],

                    [ true,  'EndsWith',       'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox/47.0' ],
                    [ false,  'EndsWith',      'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox' ],

        ];
    }
}
