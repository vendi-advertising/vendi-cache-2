<?php

namespace Vendi\Cache\Tests\CacheOptions;

use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\CacheBypasses\AbstractCacheBypass;
use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

use Vendi\Cache\CacheOptions\AbstractCacheOption;
use Vendi\Cache\Tests\vendi_cache_test_base;

abstract class generic_child_class_of_AbstractCacheOption extends AbstractCacheOption
{
    public function __construct( Maestro $maestro )
    {
        parent::__construct( $maestro->get_secretary() );
    }

    public function get_default_value()
    {
        return 'CHEESE';
    }

    public function get_potential_options()
    {
        return [
                     'CHEESE' => 'American',
                     'MEAT'   => 'Cow',
            ];
    }

    public function get_description()
    {
        return 'Test Child Class';
    }

    public function get_storage_name()
    {
        return 'test-child-class';
    }
}

final class radio_child_class_of_AbstractCacheOption extends generic_child_class_of_AbstractCacheOption
{
    public function get_option_type()
    {
        return self::OPTION_TYPE_RADIO;
    }
}

final class checkbox_child_class_of_AbstractCacheOption extends generic_child_class_of_AbstractCacheOption
{
    public function get_option_type()
    {
        return self::OPTION_TYPE_CHECKBOX;
    }

    public function get_true_value()
    {
        return 'CHEESE';
    }
}

final class unsupport_child_class_of_AbstractCacheOption extends generic_child_class_of_AbstractCacheOption
{
    public function get_option_type()
    {
        return 'TRIANGLE';
    }
}

class test_AbstractCacheOption extends vendi_cache_test_base
{
    private function _get_mock_radio()
    {
        return new radio_child_class_of_AbstractCacheOption( $this->__get_new_maestro() );
    }

    private function _get_mock_checkbox()
    {
        return new checkbox_child_class_of_AbstractCacheOption( $this->__get_new_maestro() );
    }

    private function _get_mock_unsupported()
    {
        return new unsupport_child_class_of_AbstractCacheOption( $this->__get_new_maestro() );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\AbstractCacheOption::__construct
     */
    public function test___construct()
    {
        $mock = $this->_get_mock_radio();
        $this->assertInstanceOf( '\\Vendi\Cache\CacheOptions\\AbstractCacheOption', $mock );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\AbstractCacheOption::is_value_valid
     */
    public function test_is_value_valid()
    {
        $mock = $this->_get_mock_radio();

        $this->assertTrue( $mock->is_value_valid( 'CHEESE' ) );
        $this->assertTrue( $mock->is_value_valid( 'MEAT' ) );
        $this->assertFalse( $mock->is_value_valid( 'cheese' ) );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\AbstractCacheOption::is_value_valid
     */
    public function test_is_value_valid__null()
    {
        $this->setExpectedException( '\\Assert\\InvalidArgumentException', 'Value "<NULL>" is empty, but non empty value was expected' );

        $mock = $this->_get_mock_radio();
        $mock->is_value_valid( null );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\AbstractCacheOption::get_true_value
     */
    public function test_get_true_value()
    {
        $this->setExpectedException( '\\Exception', 'Child classes that are checkboxes must implement this method' );

        $mock = $this->_get_mock_radio();
        $mock->get_true_value();
    }

    /**
     * @covers Vendi\Cache\CacheOptions\AbstractCacheOption::_render_as_radio_buttons
     * @covers Vendi\Cache\CacheOptions\AbstractCacheOption::get_html
     */
    public function test__render_as_radio_buttons()
    {
        $mock = $this->_get_mock_radio();
        $html = $mock->get_html( );

        $expected = '<h2>Test Child Class</h2><label for="test-child-class-CHEESE"><input type="radio" name="test-child-class" id="test-child-class-CHEESE" value="CHEESE"  checked="checked" />American</label><br /><label for="test-child-class-MEAT"><input type="radio" name="test-child-class" id="test-child-class-MEAT" value="MEAT"  />Cow</label><br />';

        $this->assertSame( $expected, $html );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\AbstractCacheOption::_render_as_checkboxes
     * @covers Vendi\Cache\CacheOptions\AbstractCacheOption::get_html
     */
    public function test__get_mock_checkbox()
    {
        $mock = $this->_get_mock_checkbox();
        $html = $mock->get_html( );

        $expected = '<label for="test-child-class">Test Child Class<input type="checkbox" name="test-child-class" id="test-child-class" value="true"  checked="checked" /></label>';

        $this->assertSame( $expected, $html );
    }

    /**
     * @covers Vendi\Cache\CacheOptions\AbstractCacheOption::get_html
     */
    public function test__get_mock_unsupoorted()
    {
        $this->setExpectedException( '\\Exception', 'Unknown option type: TRIANGLE' );

        $mock = $this->_get_mock_unsupported();
        $html = $mock->get_html( );
    }
}
