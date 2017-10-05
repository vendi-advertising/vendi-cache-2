<?php

namespace Vendi\Cache\Tests;

use Monolog\Handler\NullHandler;

use Vendi\Cache\{DefaultSettings, Maestro};
use Vendi\Cache\CacheBypasses\AjaxMode;

class cache_bypass_base extends \PHPUnit_Framework_TestCase
{
    public function __get_maestro()
    {
        return ( new Maestro() )
                ->with_cache_settings( new \Vendi\Cache\Tests\non_global_constant_cache_settings() )
                ->with_logger(
                                new \Monolog\Logger(
                                                'vendi-cache-noop',
                                                array( new NullHandler( ) )
                                            )
                 )
            ;
    }
}
