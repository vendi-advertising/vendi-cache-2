<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\AjaxMode;

class test_CacheBypasses_AjaxMode extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__DOING_AJAX__true()
    {
        $maestro = $this->__get_maestro();
        $cache_settings = $maestro->get_cache_settings();
        $cache_settings->reset_all();

        $this->assertFalse( $cache_settings->is_constant_defined( 'DOING_AJAX' ) );
        $cache_settings->set_constant( 'DOING_AJAX', true );
        $test = new AjaxMode( $maestro );
        $result = $test->is_cacheable();
        $this->assertFalse( $result );
        $cache_settings->reset_all();
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__DOING_AJAX__false()
    {
        $maestro = $this->__get_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $this->assertFalse( $cache_settings->is_constant_defined( 'DOING_AJAX' ) );
        $cache_settings->set_constant( 'DOING_AJAX', false );
        $test = new AjaxMode( $maestro );
        $result = $test->is_cacheable();
        $this->assertTrue( $result );
        $cache_settings->reset_all();
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__wp_doing_ajax__false()
    {
        $maestro = $this->__get_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $this->assertFalse( $cache_settings->is_function_defined( 'wp_doing_ajax' ) );
        $cache_settings->set_function( 'wp_doing_ajax', function( ){ return false; } );
        $test = new AjaxMode( $maestro );
        $result = $test->is_cacheable();
        $this->assertTrue( $result );
        $cache_settings->reset_all();
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__wp_doing_ajax__true()
    {
        $maestro = $this->__get_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $this->assertFalse( $cache_settings->is_function_defined( 'wp_doing_ajax' ) );
        $cache_settings->set_function( 'wp_doing_ajax', function( ){ return true; } );
        $test = new AjaxMode( $maestro );
        $result = $test->is_cacheable();
        $this->assertFalse( $result );
        $cache_settings->reset_all();
    }


}
