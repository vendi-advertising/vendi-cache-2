<?php

namespace Vendi\Cache\Tests;

use Monolog\Handler\NullHandler;
use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\DefaultSettings;
use Vendi\Cache\Maestro;

class cache_bypass_base extends \PHPUnit_Framework_TestCase
{
    private $_dirs = array();

    private $_files = array();

    public function tearDown()
    {
        parent::tearDown();

        foreach( $this->_files as $f )
        {
            if( is_file( $f ) )
            {
                unlink( $f );
            }
        }

        foreach( $this->_dirs as $d )
        {
            if( is_dir( $d ) )
            {
                rmdir( $d );
            }
        }
    }

    public function __get_new_maestro( Request $request = null )
    {
        return ( new Maestro() )
                ->with_cache_settings( new \Vendi\Cache\Tests\non_global_constant_cache_settings() )
                ->with_request( $request ? $request : Maestro::get_default_request() )
                ->with_logger(
                                new \Monolog\Logger(
                                                'vendi-cache-noop',
                                                array( new NullHandler( ) )
                                            )
                 )
            ;
    }

    public function touch_file( $path )
    {
        touch( $path );
        $this->_files[] = $path;
    }

    //https://stackoverflow.com/a/1707859/231316
    public function create_temp_dir()
    {
        $tempfile = tempnam( sys_get_temp_dir(), 'VC2' );
        if( false === $tempfile )
        {
            throw new \Exception( 'Could not create file for temporary directory' );
        }

        if( file_exists( $tempfile ) )
        {
            unlink( $tempfile );
        }

        mkdir( $tempfile );

        if( ! is_dir( $tempfile ) )
        {
            throw new \Exception( 'Could not create temporary directory' );
        }

        $this->_dirs[] = $tempfile;

        return $tempfile;
    }

    public function _test_constant_not_defined( $class_to_test, $name )
    {
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $this->assertFalse( $cache_settings->is_constant_defined( $name ) );
        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $result = $test->is_cacheable();
        $this->assertTrue( $result );
    }

    public function _test_constant_defined_set_to_boolean( $class_to_test, $name, $value, $is_cacheable )
    {
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $this->assertFalse( $cache_settings->is_constant_defined( $name ) );
        $cache_settings->set_constant( $name, $value );
        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $result = $test->is_cacheable();
        $this->assertSame( $is_cacheable, $result );
    }

    public function _test_function_not_defined( $class_to_test, $name )
    {
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $this->assertFalse( $cache_settings->is_function_defined( $name ) );
        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $result = $test->is_cacheable();
        $this->assertTrue( $result );
    }

    public function _test_function_defined_returns_boolean( $class_to_test, $name, $value, $is_cacheable )
    {
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $this->assertFalse( $cache_settings->is_function_defined( $name ) );
        $cache_settings->set_function( $name, function( ) use ( $value ) { return $value; } );
        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $result = $test->is_cacheable();
        $this->assertSame( $is_cacheable, $result );
    }
}
