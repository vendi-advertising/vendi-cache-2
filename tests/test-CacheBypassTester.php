<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheBypassTester;

class test_CacheBypassTester extends \WP_UnitTestCase
{

    /**
     * @covers Vendi\Cache\CacheBypassTester::get_instance
     */
    public function test_get_instance()
    {
        $this->assertInstanceOf( '\Vendi\Cache\CacheBypassTester', CacheBypassTester::get_instance() );
    }

    /**
     * @covers Vendi\Cache\CacheBypassTester::test_request
     */
    public function test_test_request()
    {
        $this->assertTrue( is_bool( CacheBypassTester::get_instance()->test_request() ) );
    }

}
