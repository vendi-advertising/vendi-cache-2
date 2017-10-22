<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheOptions;

use Vendi\Cache\CacheOptions\CacheOptionInterface;
use Vendi\Cache\CacheOptions\DebugComment;
use Vendi\Cache\Tests\vendi_cache_test_base;

/**
 * @group CacheOptions
 */
class test_DebugComment extends vendi_cache_test_base
{
    private function _get_test_object()
    {
        return (new DebugComment($this->__get_new_maestro()->get_secretary()));
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\DebugComment::get_option_type
     */
    public function test_get_option_type()
    {
        $this->assertSame(CacheOptionInterface::OPTION_TYPE_CHECKBOX, $this->_get_test_object()->get_option_type());
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\DebugComment::get_default_value
     */
    public function test_get_default_value()
    {
        $this->assertSame(DebugComment::COMMENT_ON, $this->_get_test_object()->get_default_value());
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\DebugComment::get_description
     */
    public function test_get_description()
    {
        $this->assertSame('Add a hidden HTML comment to the bottom of every page.', $this->_get_test_object()->get_description());
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\DebugComment::get_storage_name
     */
    public function test_get_storage_name()
    {
        $this->assertSame('debug-comment', $this->_get_test_object()->get_storage_name());
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\DebugComment::get_true_value
     */
    public function test_get_true_value()
    {
        $this->assertSame(DebugComment::COMMENT_ON, $this->_get_test_object()->get_true_value());
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\DebugComment::get_potential_options
     */
    public function test_get_potential_options()
    {
        $expected = [
                        DebugComment::COMMENT_ON,
                        DebugComment::COMMENT_OFF,
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
