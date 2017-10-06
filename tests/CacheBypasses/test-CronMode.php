<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\CronMode;

class test_CacheBypasses_CronMode extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::is_cacheable
     */
    public function test_is_cacheable__DOING_CRON__not_defined()
    {
        $this->_test_constant_not_defined( 'CronMode', 'DOING_CRON' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::is_cacheable
     */
    public function test_is_cacheable__DOING_CRON__true()
    {
        $this->_test_constant_defined_set_to_boolean( 'CronMode', 'DOING_CRON', true, false );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::is_cacheable
     */
    public function test_is_cacheable__DOING_CRON__false()
    {
        $this->_test_constant_defined_set_to_boolean( 'CronMode', 'DOING_CRON', false, true );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::is_cacheable
     */
    public function test_is_cacheable__wp_doing_cron__not_defined()
    {
        $this->_test_function_not_defined( 'CronMode', 'wp_doing_cron' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::is_cacheable
     */
    public function test_is_cacheable__wp_doing_cron__false()
    {
        $this->_test_function_defined_returns_boolean( 'CronMode', 'wp_doing_cron', true, false );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::is_cacheable
     */
    public function test_is_cacheable__wp_doing_cron__true()
    {
        $this->_test_function_defined_returns_boolean( 'CronMode', 'wp_doing_cron', false, true );
    }

}
