<?php

namespace Vendi\Cache\Tests;

use Monolog\Handler\NullHandler;

use Vendi\Cache\{DefaultSettings, Maestro};

class cache_bypass_base extends \PHPUnit_Framework_TestCase
{
    public function __get_new_maestro()
    {
        return ( new Maestro() )
                ->with_cache_settings( new \Vendi\Cache\Tests\non_global_constant_cache_settings() )
                ->with_logger(
                                new \Monolog\Logger(
                                                'vendi-cache-noop',
                                                array( new NullHandler( ) )
                                            )
                 )
            ;
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
