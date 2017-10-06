<?php

namespace Vendi\Cache\Tests;

use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\{CacheBypassTester, CacheSettingsInterface, Maestro};

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

    /**
     * @covers Vendi\Cache\CacheBypassTester::__construct
     * @covers Vendi\Cache\CacheBypassTester::get_maestro
     * @covers Vendi\Cache\CacheBypassTester::get_logger
     * @covers Vendi\Cache\CacheBypassTester::get_cache_settings
     * @covers Vendi\Cache\CacheBypassTester::get_request
     */
    public function test__various_methods()
    {
        $maestro = Maestro::get_default_instance();
        $tester = new CacheBypassTester( $maestro );
        $this->assertInstanceOf( Maestro::class, $tester->get_maestro() );
        $this->assertInstanceOf( Request::class, $tester->get_request() );
        $this->assertInstanceOf( Logger::class, $tester->get_logger() );
        $this->assertInstanceOf( CacheSettingsInterface::class, $tester->get_cache_settings() );
    }

}
