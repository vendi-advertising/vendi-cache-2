<?php

namespace Vendi\Cache\Tests;

class test_CacheBypasses_WpInstalling extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\WpInstalling::is_cacheable
     */
    public function test_is_cacheable__const_WP_INSTALLING__not_defined()
    {
        $this->_test_constant_not_defined( 'WpInstalling', 'WP_INSTALLING' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\WpInstalling::is_cacheable
     */
    public function test_is_cacheable__const_WP_INSTALLING__true()
    {
        $this->_test_constant_defined_set_to_boolean( 'WpInstalling', 'WP_INSTALLING', true, false );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\WpInstalling::is_cacheable
     */
    public function test_is_cacheable__const_WP_INSTALLING__false()
    {
        $this->_test_constant_defined_set_to_boolean( 'WpInstalling', 'WP_INSTALLING', false, true );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\WpInstalling::is_cacheable
     */
    public function test_is_cacheable__wp_installing__not_defined()
    {
        $this->_test_function_not_defined( 'WpInstalling', 'wp_installing' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\WpInstalling::is_cacheable
     */
    public function test_is_cacheable__wp_installing__false()
    {
        $this->_test_function_defined_returns_boolean( 'WpInstalling', 'wp_installing', true, false );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\WpInstalling::is_cacheable
     */
    public function test_is_cacheable__wp_installing__true()
    {
        $this->_test_function_defined_returns_boolean( 'WpInstalling', 'wp_installing', false, true );
    }

}
