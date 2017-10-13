<?php

namespace Vendi\Cache\Tests;

class test_CacheBypasses_WpInstalling extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\WpInstalling::is_cacheable
     */
    public function test_is_cacheable__const_WP_INSTALLING__not_defined()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined_or_is_but_set_to_false( 'WpInstalling', 'WP_INSTALLING', 'wp_installing' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\WpInstalling::is_cacheable
     */
    public function test_is_cacheable__wp_installing__not_defined()
    {
        $this->_test_is_cacheable_because_required_function_defined_and_returns_true( 'WpInstalling', 'wp_installing' );
    }

}
