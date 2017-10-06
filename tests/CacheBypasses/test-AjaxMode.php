<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\AjaxMode;

class test_CacheBypasses_AjaxMode extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__DOING_AJAX__not_defined()
    {
        $this->_test_constant_not_defined( 'DOING_AJAX' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__DOING_AJAX__true()
    {
        $this->_test_constant_defined_set_to_boolean( 'DOING_AJAX', false, true );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__DOING_AJAX__false()
    {
        $this->_test_constant_defined_set_to_boolean( 'DOING_AJAX', true, false );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__wp_doing_ajax__not_defined()
    {
        $this->_test_function_not_defined( 'wp_doing_ajax' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__wp_doing_ajax__false()
    {
        $this->_test_function_defined_returns_boolean( 'wp_doing_ajax', true, false );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__wp_doing_ajax__true()
    {
        $this->_test_function_defined_returns_boolean( 'wp_doing_ajax', false, true );
    }

}
