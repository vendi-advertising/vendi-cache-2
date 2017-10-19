<?php

namespace Vendi\Cache\Tests;

class test_CacheBypasses_CronMode extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::is_cacheable
     */
    public function test_is_cacheable__DOING_CRON__not_defined()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined( 'CronMode', 'DOING_CRON' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::is_cacheable
     */
    // public function test_is_cacheable_because_required_function_defined_and_returns_true()
    // {
    //     $this->_test_is_cacheable_because_required_function_defined_and_returns_true( 'CronMode', 'wp_doing_cron' );
    // }

}
