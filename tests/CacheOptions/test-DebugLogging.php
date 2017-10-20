<?php

namespace Vendi\Cache\Tests\CacheOptions;

use Vendi\Cache\Maestro;
use Vendi\Cache\CacheOptions\DebugLogging;
use Vendi\Cache\CacheOptions\CacheOptionInterface;
use Vendi\Cache\Tests\vendi_cache_test_base;

class test_DebugLogging extends vendi_cache_test_base
{
    private function _get_test_object()
    {
        return ( new DebugLogging( $this->__get_new_maestro()->get_secretary() ) );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\DebugLogging::get_option_type
     */
    public function test_get_option_type()
    {
        $this->assertSame( CacheOptionInterface::OPTION_TYPE_RADIO, $this->_get_test_object()->get_option_type() );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\DebugLogging::get_default_value
     */
    public function test_get_default_value()
    {
        $this->assertSame( DebugLogging::LOGGING_OFF, $this->_get_test_object()->get_default_value() );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\DebugLogging::get_description
     */
    public function test_get_description()
    {
        $this->assertSame( 'Debug Logging', $this->_get_test_object()->get_description() );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\DebugLogging::get_storage_name
     */
    public function test_get_storage_name()
    {
        $this->assertSame( 'debug-logging', $this->_get_test_object()->get_storage_name() );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\DebugLogging::get_potential_options
     */
    public function test_get_potential_options()
    {
        $expected = [
                        DebugLogging::LOGGING_OFF => 'Debug logging off',
                        DebugLogging::LOG_TO_DATEBASE => 'Log to database',
                        DebugLogging::LOG_TO_FILE => 'Log to file',
                        DebugLogging::LOG_TO_DATEBASE_AND_FILE => 'Log to datebase and file',
            ];

        $result = $this->_get_test_object()->get_potential_options();

        $this->assertInternalType( 'array', $result );

        $this->assertTrue(
                            $this->arrays_are_similar(
                                                        $expected,
                                                        $result
                            )
            );
    }
}
