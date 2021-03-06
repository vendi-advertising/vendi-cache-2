<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheOptions;

use Vendi\Cache\Tests\vendi_cache_test_base_no_wordpress;

require_once VENDI_CACHE_DIR . '/tests/includes/classes-for-cache-options.php';

/**
 * @group CacheOptions
 */
class test_AbstractCacheOption extends vendi_cache_test_base_no_wordpress
{
    private function _get_mock_radio()
    {
        return new radio_child_class_of_AbstractCacheOption($this->__get_new_maestro());
    }

    private function _get_mock_checkbox()
    {
        return new checkbox_child_class_of_AbstractCacheOption($this->__get_new_maestro());
    }

    private function _get_mock_unsupported()
    {
        return new unsupport_child_class_of_AbstractCacheOption($this->__get_new_maestro());
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\AbstractCacheOption::__construct
     */
    public function test___construct()
    {
        $mock = $this->_get_mock_radio();
        $this->assertInstanceOf('\\Vendi\\Cache\\CacheOptions\\AbstractCacheOption', $mock);
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\AbstractCacheOption::is_value_valid
     */
    public function test_is_value_valid()
    {
        $mock = $this->_get_mock_radio();

        $this->assertTrue($mock->is_value_valid('CHEESE'));
        $this->assertTrue($mock->is_value_valid('MEAT'));
        $this->assertFalse($mock->is_value_valid('cheese'));

        $mock = $this->_get_mock_checkbox();
        $this->assertTrue($mock->is_value_valid('Cow'));


        $mock = $this->_get_mock_unsupported();
        $this->assertFalse($mock->is_value_valid('Cow'));
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\AbstractCacheOption::get_true_value
     */
    public function test_get_true_value()
    {
        $this->setExpectedException('\\Exception', 'Child classes that are checkboxes must implement this method');

        $mock = $this->_get_mock_radio();
        $mock->get_true_value();
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\AbstractCacheOption::_render_as_radio_buttons
     * @covers \Vendi\Cache\CacheOptions\AbstractCacheOption::get_html
     */
    public function test__render_as_radio_buttons()
    {
        $mock = $this->_get_mock_radio();
        $html = $mock->get_html();

        $expected = '<h2>Test Child Class</h2><label for="test-child-class-CHEESE"><input type="radio" name="test-child-class" id="test-child-class-CHEESE" value="CHEESE"  checked="checked" />American</label><br /><label for="test-child-class-MEAT"><input type="radio" name="test-child-class" id="test-child-class-MEAT" value="MEAT"  />Cow</label><br />';

        $this->assertSame($expected, $html);
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\AbstractCacheOption::_render_as_checkboxes
     * @covers \Vendi\Cache\CacheOptions\AbstractCacheOption::get_html
     */
    public function test__get_mock_checkbox()
    {
        $mock = $this->_get_mock_checkbox();
        $html = $mock->get_html();

        $expected = '<label for="test-child-class">Test Child Class<input type="checkbox" name="test-child-class" id="test-child-class" value="true"  checked="checked" /></label>';

        $this->assertSame($expected, $html);
    }

    /**
     * @covers \Vendi\Cache\CacheOptions\AbstractCacheOption::get_html
     */
    public function test__get_mock_unsupoorted()
    {
        $this->setExpectedException('\\Exception', 'Unknown option type: TRIANGLE');

        $mock = $this->_get_mock_unsupported();
        $html = $mock->get_html();
    }
}
