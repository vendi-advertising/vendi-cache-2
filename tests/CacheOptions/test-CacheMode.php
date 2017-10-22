<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheOptions;

use Vendi\Cache\CacheOptions\CacheMode;
use Vendi\Cache\CacheOptions\CacheOptionInterface;
use Vendi\Cache\Tests\vendi_cache_test_base;

/**
 * @group CacheOptions
 */
class test_CacheMode extends vendi_cache_test_base
{
    private function _get_test_object()
    {
        return (new CacheMode($this->__get_new_maestro()->get_secretary()));
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\CacheMode::get_option_type
     */
    public function test_get_option_type()
    {
        $this->assertSame(CacheOptionInterface::OPTION_TYPE_RADIO, $this->_get_test_object()->get_option_type());
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\CacheMode::get_default_value
     */
    public function test_get_default_value()
    {
        $this->assertSame(CacheMode::MODE_OFF, $this->_get_test_object()->get_default_value());
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\CacheMode::get_description
     */
    public function test_get_description()
    {
        $this->assertSame('Cache Mode', $this->_get_test_object()->get_description());
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\CacheMode::get_storage_name
     */
    public function test_get_storage_name()
    {
        $this->assertSame('cache-mode', $this->_get_test_object()->get_storage_name());
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\CacheMode::get_potential_options
     */
    public function test_get_potential_options()
    {
        $expected = [
                        CacheMode::MODE_OFF => 'Disable Vendi Cache',
                        CacheMode::MODE_ON  => 'Enable Vendi Cache',
            ];

        $result = $this->_get_test_object()->get_potential_options();

        $this->assertInternalType('array', $result);

        $this->assertTrue(
                            $this->arrays_are_similar(
                                                        $expected,
                                                        $result
                            )
            );
    }
}
