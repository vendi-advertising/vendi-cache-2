<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheKeyGenerator;
use Vendi\Shared\utils;

class test_CacheKeyGenerator extends \WP_UnitTestCase
{
    private $OLD_POST;
    private $OLD_GET;
    private $OLD_COOKIE;
    private $OLD_SERVER;

    public function setUp()
    {
        parent::setUp();
        $this->OLD_COOKIE = isset( $_COOKIE ) ? $_COOKIE : null;
        $this->OLD_SERVER = isset( $_SERVER ) ? $_SERVER : null;
        $this->OLD_GET    = isset( $_GET )    ? $_GET    : null;
        $this->OLD_POST   = isset( $_POST )   ? $_POST   : null;
    }

    public function tearDown()
    {
        $_COOKIE = $this->OLD_COOKIE;
        $_SERVER = $this->OLD_SERVER;
        $_GET    = $this->OLD_GET;
        $_POST   = $this->OLD_POST;
        parent::tearDown();
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
        utils::$CUSTOM_SERVER = [
                                    'HTTPS'         => 'on',
                                    'HTTP_HOST'     => 'www.example.net',
                                    'REQUEST_URI'   => '/',
                            ];

        $this->assertSame( 'www.example.net_/~~~~_vendi_cache_https.html', CacheKeyGenerator::local_cache_filename_from_url());
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
     * @covers Vendi\Cache\CacheKeyGenerator::create_url_from_server_variables
     * @dataProvider provider_for_create_url
     */
    public function test_create_url_from_server_variables( $expected, $keys )
    {
        utils::$CUSTOM_SERVER = $keys;
        $this->assertSame( $expected, CacheKeyGenerator::create_url_from_server_variables( ) );
    }

    public function provider_for_create_url()
    {
        return [
                    //url,                              server keys

                    //HTTPS support
                    [ 'https://www.example.com/cheese', [ 'HTTPS' => '1',   'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],
                    [ 'https://www.example.com/cheese', [ 'HTTPS' => 'on',  'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],
                    [ 'https://www.example.com/cheese', [ 'HTTPS' => 'abc', 'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],

                    //Non-HTTPS
                    [ 'http://www.example.com/cheese', [ 'HTTPS' => 'off',  'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],
                    [ 'http://www.example.com/cheese', [ 'HTTPS' => 'no',   'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],
                    [ 'http://www.example.com/cheese', [                    'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese' ] ],

                    //Weird fallback for missing host.
                    //TODO: Drop support for SERVER_NAME?
                    //https://github.com/vendi-advertising/vendi-cache-2/issues/1
                    [ 'http://server/cheese',          [ 'SERVER_NAME' => 'server', 'REQUEST_URI' => '/cheese' ] ],

                    //Weird path to path example.
                    //TODO: Drop support for PATH_INFO?
                    //https://github.com/vendi-advertising/vendi-cache-2/issues/2
                    [ 'http://www.example.com/cheese.php/more',          [ 'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese.php', 'PATH_INFO' => '/more' ] ],

                    //QueryString, test with and without ?
                    [ 'http://www.example.com/cheese?a=b', [                    'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese', 'QUERY_STRING' => '?a=b' ] ],
                    [ 'http://www.example.com/cheese?a=b', [                    'HTTP_HOST' => 'www.example.com', 'REQUEST_URI' => '/cheese', 'QUERY_STRING' => 'a=b' ] ],
            ];
    }

    public function provider_for_host_cache()
    {
        return [
                    //Clean                 Dirty
                    [ '',                   '' ],
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
