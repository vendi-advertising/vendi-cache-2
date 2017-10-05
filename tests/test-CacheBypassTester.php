<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\{CacheBypassTester, Maestro};

class test_CacheBypassTester extends \WP_UnitTestCase
{

    /**
     * @covers Vendi\Cache\CacheBypassTester::test_request
     */
    public function test_test_request()
    {
        $maestro = Maestro::get_default_instance();

        $tester = new CacheBypassTester( $maestro );

        $this->assertTrue( is_bool( $tester->test_request() ) );
    }

}
