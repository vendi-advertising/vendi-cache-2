<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\WpInstalling;

class test_CacheBypasses_WpInstalling extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\WpInstalling::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstantAndFunction::is_resource_not_cacheable_because_constant_is_true
     */
    public function test_is_resource_not_cacheable__const_WP_INSTALLING__not_defined()
    {
        $this->_test_is_cacheable_because_fatal_constant_not_defined('WpInstalling', 'WP_INSTALLING');
    }

    /**
     * @covers \Vendi\Cache\CacheBypasses\WpInstalling::__construct
     * @covers \Vendi\Cache\CacheBypasses\WpInstalling::get_constant
     */
    public function test___construct()
    {
        $tester = new WpInstalling($this->__get_new_maestro(), 'WP_INSTALLING');
        $this->assertSame('WP_INSTALLING', $tester->get_constant());
    }

    /**
     * @covers \Vendi\Cache\CacheBypasses\WpInstalling::is_resource_not_cacheable_because_function_says_so
     */
    public function test_test_specific_function_and_log_failure()
    {
        $tester = new WpInstalling($this->__get_new_maestro(), 'WP_INSTALLING');

        wp_installing(true);
        $this->assertTrue($tester->is_resource_not_cacheable_because_function_says_so());

        wp_installing(false);
        $this->assertFalse($tester->is_resource_not_cacheable_because_function_says_so());
    }
}
