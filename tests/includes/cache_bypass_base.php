<?php

namespace Vendi\Cache\Tests;

use Monolog\Handler\NullHandler;
use Symfony\Component\HttpFoundation\Request;
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

    public function _test_is_cacheable_because_fatal_constant_not_defined_or_is_but_set_to_false( $class_to_test, $name, $func_name )
    {
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_cache_settings();

        if( $func_name )
        {
            $cache_settings->set_function( $func_name, function( ) { return false; } );
        }

        //The supplied constant should not exist by default
        $this->assertFalse( $cache_settings->is_constant_defined( $name ) );

        //If the constant doesn't exist then we assume the resource is cacheable
        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $this->assertTrue( $test->is_cacheable() );

        $cache_settings->set_constant( $name, true );

        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $this->assertFalse( $test->is_cacheable() );

        //Constant is define but weirdly set to false. This me
        $cache_settings->set_constant( $name, false );

        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $this->assertTrue( $test->is_cacheable() );
    }

    public function _test_is_cacheable_because_required_function_defined_and_returns_true( $class_to_test, $name )
    {
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_cache_settings();

        //The supplied function should not exist by default
        $this->assertFalse( $cache_settings->is_function_defined( $name ) );

        //The function is required to exist so this pass should always return false
        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $result = $test->is_cacheable();
        $this->assertFalse( $result );

        //Now create the function and have it return true
        $cache_settings->set_function( $name, function( ) { return true; } );

        //Test the result against what is provided
        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $result = $test->is_cacheable();
        $this->assertFalse( $result );

        //Now create the function and have it return whatever we provided
        $cache_settings->set_function( $name, function( ) { return false; } );

        //Test the result against what is provided
        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $result = $test->is_cacheable();
        $this->assertTrue( $result );
    }
}
