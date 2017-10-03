<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheKeyGenerator;

class test_CacheKeyGenerator extends \WP_UnitTestCase
{

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::get_cache_lookup_counts_for_url
     * @covers Vendi\Cache\CacheKeyGenerator::local_cache_filename_from_url
     */
    public function test_get_cache_lookup_counts_for_url()
    {
        $url = 'https://www.example.com/test_get_cache_lookup_counts_for_url';
        $this->assertSame( -1, CacheKeyGenerator::get_cache_lookup_counts_for_url( $url ) );
        CacheKeyGenerator::local_cache_filename_from_url( $url );
        $this->assertSame( 0, CacheKeyGenerator::get_cache_lookup_counts_for_url( $url ) );
        CacheKeyGenerator::local_cache_filename_from_url( $url );
        $this->assertSame( 1, CacheKeyGenerator::get_cache_lookup_counts_for_url( $url ) );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::sanitize_path_for_cache_filename
     * @dataProvider provider_for_sanitize_path
     */
    public function test_sanitize_path_for_cache_filename( $expected, $value )
    {
        $this->assertSame( $expected, CacheKeyGenerator::sanitize_path_for_cache_filename( $value ) );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::local_cache_filename_from_url
     */
    public function test_local_cache_filename_from_url__naked( )
    {
        $expected = CacheKeyGenerator::local_cache_filename_from_url( $this->__get_test_url() );
        $this->assertSame( $expected, CacheKeyGenerator::local_cache_filename_from_url( ) );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::local_cache_filename_from_url
     * @dataProvider provider_for_local_cache_filename
     */
    public function test_local_cache_filename_from_url( $expected, $value )
    {
        $this->assertSame( $expected, CacheKeyGenerator::local_cache_filename_from_url( $value ) );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::local_cache_filename_from_url
     */
    public function test_local_cache_filename_from_url__from_global()
    {
        $local_SERVER = [
                            'HTTPS'         => 'on',
                            'HTTP_HOST'     => 'www.example.net',
                            'REQUEST_URI'   => '/',
                    ];

        $this->assertSame( 'www.example.net_/~~~~_vendi_cache_https.html', CacheKeyGenerator::local_cache_filename_from_url( array( 'SERVER' => $local_SERVER ) ));
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::sanitize_host_for_cache_filename
     * @dataProvider provider_for_host_cache
     */
    public function test_sanitize_host_for_cache_filename( $clean, $dirty )
    {
        $this->assertSame( $clean, CacheKeyGenerator::sanitize_host_for_cache_filename( $dirty ) );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::sanitize_host_for_cache_filename
     */
    public function test_sanitize_host_for_cache_filename__empty( )
    {
        $this->expectException( '\Assert\InvalidArgumentException' );
        CacheKeyGenerator::sanitize_host_for_cache_filename( '' );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::create_url_from_server_variables
     * @dataProvider provider_for_create_url
     */
    public function test_create_url_from_server_variables( $expected, $keys )
    {
        $this->assertSame( $expected, CacheKeyGenerator::create_url_from_server_variables( array( 'SERVER' => $keys ) ) );
    }

    private function __get_test_url()
    {
        $this->assertTrue( defined( 'PLUGINDIR' ) );
        $this->assertTrue( defined( 'WP_PLUGIN_URL' ) );

        //This is kinda hacky but works.
        //Technically I could get rid of the "-1" and concatenation but I think
        //this is more proper.
        return substr( WP_PLUGIN_URL, 0, strpos( WP_PLUGIN_URL, PLUGINDIR ) - 1 ) . '/';
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::create_url_from_server_variables
     */
    public function test_create_url_from_server_variables__naked( )
    {
        $expected = $this->__get_test_url();
        $this->assertSame( $expected, CacheKeyGenerator::create_url_from_server_variables( ) );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::get_mapping_of_urls_to_files
     */
    public function test_get_mapping_of_urls_to_files( )
    {
        $this->assertInternalType( 'array', CacheKeyGenerator::get_mapping_of_urls_to_files() );
    }

    public function provider_for_create_url()
    {
        return [
                    //url,                              server keys

                    //HTTPS support
                    [ 'https://www.example.com/cheese', [ 'HTTPS' => '1',     'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],
                    [ 'https://www.example.com/cheese', [ 'HTTPS' => 'on',    'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],
                    [ 'https://www.example.com/cheese', [ 'HTTPS' => 'ssl',   'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],
                    [ 'https://www.example.com/cheese', [ 'HTTPS' => 'https', 'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],

                    //Weird one that technically works, see:
                    //https://github.com/symfony/http-foundation/blob/master/Request.php#L1111
                    [ 'https://www.example.com/cheese', [ 'HTTPS' => 'abc',   'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],

                    //Non-HTTPS
                    [ 'http://www.example.com/cheese', [ 'HTTPS' => 'off',  'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],
                    [ 'http://www.example.com/cheese', [                    'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],

                    //Weird fallback for missing host.
                    //TODO: Drop support for SERVER_NAME?
                    //https://github.com/vendi-advertising/vendi-cache-2/issues/1
                    //TODO: I think the colon-blank is a glitch in Symfony for an
                    //unsupported scenario, hacking for now.
                    [ 'http://server:/cheese',          [ 'SERVER_NAME' => 'server', 'REQUEST_URI' => '/cheese' ] ],

                    //Weird path to path example.
                    //TODO: Drop support for PATH_INFO?
                    //https://github.com/vendi-advertising/vendi-cache-2/issues/2
                    // [ 'http://www.example.com/cheese.php/more',          [ 'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese.php', 'PATH_INFO' => '/more' ] ],

                    //QueryString
                    [ 'http://www.example.com/cheese?a=b&b=c', [                    'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese', 'QUERY_STRING' => 'a=b&b=c' ] ],
            ];
    }

    public function provider_for_host_cache()
    {
        return [
                    //Clean                 Dirty
                    [ 'example.com',        'example.com' ],
                    [ 'www.example.com',    'www.example.com' ],
                    [ 'example.com',        '?example.com' ],
                    [ 'test-2.example.com', 'test-2.example.com' ],
                    [ 'test2.example.com',  'test_2.example.com' ],
                    [ 'test2.example.com',  'test__2.example.com' ],
            ];
    }

    public function provider_for_local_cache_filename()
    {
        return [
                    //expected          value
                    [ 'www.example.com_cheese/test~~~~_vendi_cache_https.html', 'https://www.example.com/cheese/test/?a=b' ],
                    [ 'www.example.com_/~~~~_vendi_cache_https.html', 'https://www.example.com/' ],
            ];
    }

    public function provider_for_sanitize_path()
    {
        return[
                    //expected          value
                    [ 'cheese/test~~~~', '/cheese/test/' ],
                    [ 'a/b~c~d~e~/f/g/h/i/j/', '/a/b/c/d/e/f/g/h/i/j/' ],
            ];
    }
}
