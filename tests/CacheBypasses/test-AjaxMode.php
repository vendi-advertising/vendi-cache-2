<?php

namespace Vendi\Cache\Tests;

class test_CacheBypasses_AjaxMode extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable__DOING_AJAX__constant()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined( 'AjaxMode', 'DOING_AJAX' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    // public function test_is_cacheable__wp_doing_ajax__function()
    // {
    //     $this->_test_is_cacheable_because_required_function_defined_and_returns_true( 'AjaxMode', 'wp_doing_ajax' );
    // }

}
