<?php

namespace Vendi\Cache\Tests;

use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\CacheBypassTester;
use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

class test_CacheBypassTester extends vendi_cache_test_base
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
     * @covers Vendi\Cache\CacheBypassTester::get_secretary
     * @covers Vendi\Cache\CacheBypassTester::get_request
     */
    public function test__various_methods()
    {
        $maestro = Maestro::get_default_instance();
        $tester = new CacheBypassTester( $maestro );
        $this->assertInstanceOf( 'Vendi\Cache\Maestro', $tester->get_maestro() );
        $this->assertInstanceOf( 'Symfony\Component\HttpFoundation\Request', $tester->get_request() );
        $this->assertInstanceOf( 'Monolog\Logger', $tester->get_logger() );
        $this->assertInstanceOf( 'Vendi\Cache\Secretary', $tester->get_secretary() );
    }

}
