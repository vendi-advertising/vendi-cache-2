<?php

namespace Vendi\Cache\Tests;

use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\{CacheKeyGenerator, Maestro};

class test_CacheKeyGenerator extends \WP_UnitTestCase
{

    private function _get_cache_key_generator_from_server_vars( $server = array() )
    {
        return $this->_get_cache_key_generator( '', 'GET', [], [], [], $server );
    }

    private function _get_cache_key_generator( $url, $method = 'GET', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null )
    {
        $maestro = Maestro::get_default_instance()
                    ->with_request( Request::create( $url, $method ) )
                ;
        return new CacheKeyGenerator( $maestro );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::get_cache_lookup_counts_for_url
     * @covers Vendi\Cache\CacheKeyGenerator::local_cache_filename_from_url
     */
    public function test_get_cache_lookup_counts_for_url()
    {
        $url = 'https://www.example.com/test_get_cache_lookup_counts_for_url';

        $cache_key_generator = $this->_get_cache_key_generator( $url );

        $this->assertSame( -1, $cache_key_generator->get_cache_lookup_counts_for_url( ) );
        $cache_key_generator->local_cache_filename_from_url( );
        $this->assertSame( 0, $cache_key_generator->get_cache_lookup_counts_for_url( ) );
        $cache_key_generator->local_cache_filename_from_url( );
        $this->assertSame( 1, $cache_key_generator->get_cache_lookup_counts_for_url( ) );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::sanitize_path_for_cache_filename
     * @dataProvider provider_for_sanitize_path
     */
    public function test_sanitize_path_for_cache_filename( $expected_path, $url )
    {
        $expected_path;

        $this->assertSame( $expected_path, $this->_get_cache_key_generator( $url )->sanitize_path_for_cache_filename( ) );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::local_cache_filename_from_url
     * @dataProvider provider_for_local_cache_filename
     */
    public function test_local_cache_filename_from_url( $expected, $url )
    {
        $this->assertSame( $expected, $this->_get_cache_key_generator( $url )->local_cache_filename_from_url( ) );
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
     * @covers Vendi\Cache\CacheKeyGenerator::get_mapping_of_urls_to_files
     */
    public function test_get_mapping_of_urls_to_files( )
    {
        $this->assertInternalType( 'array', $this->_get_cache_key_generator( '' )->get_mapping_of_urls_to_files() );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::get_maestro
     */
    public function test_get_maestro()
    {
        $this->assertInstanceOf( Maestro::class, $this->_get_cache_key_generator( '' )->get_maestro() );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::get_request
     */
    public function test_get_request()
    {
        $this->assertInstanceOf( Request::class, $this->_get_cache_key_generator( '' )->get_request() );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::get_url_without_scheme_and_host
     * @dataProvider provider_for_get_url_without_scheme_and_host
     */
    public function test_get_url_without_scheme_and_host( $expected, $url )
    {
        $this->assertSame( $expected, $this->_get_cache_key_generator( $url )->get_url_without_scheme_and_host() );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::sanitize_host_for_cache_filename
     * @dataProvider provider_for_host_cache
     */
    public function test_provider_for_host_cache( $expected, $url )
    {
        $this->assertSame( $expected, $this->_get_cache_key_generator( $url )->sanitize_host_for_cache_filename() );
    }

    /**
     * @covers Vendi\Cache\CacheKeyGenerator::__construct
     * @covers Vendi\Cache\CacheKeyGenerator::get_maestro
     */
    public function test___construct( )
    {
        $cache_key_generator = new CacheKeyGenerator( Maestro::get_default_instance() );
        $this->assertInstanceOf( Maestro::class, $cache_key_generator->get_maestro() );
    }

    public function provider_for_get_url_without_scheme_and_host()
    {
        return[
                    //expected                  url

                    //A minimum of a trailing slash should always be returned
                    [ '/',                      'http://example.com' ],
                    [ '/',                      'http://example.com/' ],

                    //Regular path
                    [ '/cheese/test/',          'http://example.com/cheese/test/' ],

                    //Querystring is removed
                    [ '/cheese/test/',          'http://example.com/cheese/test/?a=b' ],

                    //Extra long
                    [ '/a/b/c/d/e/f/g/h/i/j/',  'http://example.com/a/b/c/d/e/f/g/h/i/j/' ],

                    //No trailing slash
                    [ '/a/b/c/d/e/f/g/h/i/j',   'http://example.com/a/b/c/d/e/f/g/h/i/j' ],
            ];
    }

    public function provider_for_host_cache()
    {
        return [
                    //Clean                 Dirty
                    [ 'example.com',        'http://example.com' ],
                    [ 'www.example.com',    'http://www.example.com' ],
                    [ 'test-2.example.com', 'http://test-2.example.com' ],
                    [ 'test2.example.com',  'http://test_2.example.com' ],
                    [ 'test2.example.com',  'http://test__2.example.com' ],
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
                    //expected                  value
                    [ 'cheese/test~~~~',        'http://example.com/cheese/test/' ],
                    [ 'a/b~c~d~e~/f/g/h/i/j/',  'http://example.com/a/b/c/d/e/f/g/h/i/j/' ],
            ];
    }
}
