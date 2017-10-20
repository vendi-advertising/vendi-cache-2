<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\CronMode;

class test_CacheBypasses_CronMode extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::is_resource_not_cacheable
     * @covers Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstantAndFunction::is_resource_not_cacheable_because_constant_is_true
     */
    public function test_is_resource_not_cacheable__DOING_CRON__not_defined()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined( 'CronMode', 'DOING_CRON' );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::__construct
     * @covers Vendi\Cache\CacheBypasses\CronMode::get_constant
     */
    public function test___construct()
    {
        $tester = new CronMode( $this->__get_new_maestro(), 'DOING_CRON' );
        $this->assertSame( 'DOING_CRON', $tester->get_constant() );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\CronMode::is_resource_not_cacheable_because_function_says_so
     */
    public function test_is_resource_not_cacheable_because_function_says_so()
    {
        $tester = new CronMode( $this->__get_new_maestro(), 'DOING_CRON' );

        add_filter( 'wp_doing_cron', function(){return true;}, 99998 );
        $this->assertTrue($tester->is_resource_not_cacheable_because_function_says_so() );

        add_filter( 'wp_doing_cron', function(){return false;}, 99999 );
        $this->assertFalse($tester->is_resource_not_cacheable_because_function_says_so() );
    }
}
