<?php

namespace Vendi\Cache\Tests;

use Monolog\Handler\NullHandler;

use Vendi\Cache\{DefaultSettings, Maestro};
use Vendi\Cache\CacheBypasses\AjaxMode;

class cache_bypass_base extends \PHPUnit_Framework_TestCase
{
    public function __get_maestro()
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

    public function _test_constant_not_defined( $name )
    {
        $maestro = $this->__get_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $this->assertFalse( $cache_settings->is_constant_defined( $name ) );
        $test = new AjaxMode( $maestro );
        $result = $test->is_cacheable();
        $this->assertTrue( $result );
        $cache_settings->reset_all();
    }

    public function _test_constant_defined_set_to_boolean( $name, $value, $is_cacheable )
    {
        $maestro = $this->__get_maestro();
        $cache_settings = $maestro->get_cache_settings();
        $cache_settings->reset_all();

        $this->assertFalse( $cache_settings->is_constant_defined( $name ) );
        $cache_settings->set_constant( $name, $value );
        $test = new AjaxMode( $maestro );
        $result = $test->is_cacheable();
        $this->assertSame( $is_cacheable, $result );
        $cache_settings->reset_all();
    }

    public function _test_function_not_defined( $name )
    {
        $maestro = $this->__get_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $this->assertFalse( $cache_settings->is_function_defined( $name ) );
        $test = new AjaxMode( $maestro );
        $result = $test->is_cacheable();
        $this->assertTrue( $result );
        $cache_settings->reset_all();
    }

    public function _test_function_defined_returns_boolean( $name, $value, $is_cacheable )
    {
        $maestro = $this->__get_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $this->assertFalse( $cache_settings->is_function_defined( $name ) );
        $cache_settings->set_function( $name, function( ) use ( $value ) { return $value; } );
        $test = new AjaxMode( $maestro );
        $result = $test->is_cacheable();
        $this->assertSame( $is_cacheable, $result );
        $cache_settings->reset_all();
    }
}
