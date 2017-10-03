<?php

namespace Vendi\Cache\Tests;

use Monolog\Handler\NullHandler;

use Symfony\Component\HttpFoundation\Request;

use Vendi\Cache\CacheBypasses\AjaxMode;

class test_CacheBypasses_AjaxMode extends \WP_UnitTestCase
{

    private function __get_logger()
    {
        return new \Monolog\Logger(
                                    'vendi-cache-noop',
                                    array( new NullHandler( ) )
                                );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AjaxMode::is_cacheable
     */
    public function test_is_cacheable()
    {
        $request = Request::createFromGlobals();
        $logger = $this->__get_logger();

        $test = new AjaxMode( $request, $logger );

        $result = $test->is_cacheable();

        $this->assertTrue( is_bool( $result ) );
    }
}


 // @backupGlobals enabled

