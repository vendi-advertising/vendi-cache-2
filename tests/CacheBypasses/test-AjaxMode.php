<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypasses\AjaxMode;

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
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::__construct
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::get_constant
     */
    public function test___construct()
    {
        $tester = new AjaxMode( $this->__get_new_maestro(), 'DOING_AJAX' );
        $this->assertSame( 'DOING_AJAX', $tester->get_constant() );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::test_specific_function_and_log_failure
     */
    public function test_test_specific_function_and_log_failure()
    {
        $tester = new AjaxMode( $this->__get_new_maestro(), 'DOING_AJAX' );

        add_filter( 'wp_doing_ajax', function(){return true;}, 99998 );
        $this->assertFalse($tester->test_specific_function_and_log_failure() );

        add_filter( 'wp_doing_ajax', function(){return false;}, 99999 );
        $this->assertTrue($tester->test_specific_function_and_log_failure() );
    }

}
