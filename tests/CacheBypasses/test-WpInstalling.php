<?php

namespace Vendi\Cache\Tests;

class test_CacheBypasses_WpInstalling extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\WpInstalling::is_cacheable
     */
    public function test_is_cacheable__const_WP_INSTALLING__not_defined()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined_or_set_to_true( 'WpInstalling', 'WP_INSTALLING' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\WpInstalling::is_cacheable
     */
    public function test_is_cacheable__wp_installing__not_defined()
    {
        $this->_test_is_cacheable_because_required_function_defined_and_returns_true( 'WpInstalling', 'wp_installing' );
    }

}
