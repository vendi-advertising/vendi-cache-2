<?php

namespace Vendi\Cache\CacheOptions;

use Assert\Assertion;
use Vendi\Cache\Secretary;

abstract class AbstractCacheOption implements CacheOptionInterface
{
    private $_secretary;

    public function __construct( Secretary $secretary )
    {
        $this->_secretary = $secretary;
    }

    public function get_true_value()
    {
        throw new \Exception( 'Child classes that are checkboxes must implement this method' );
    }

    public function is_value_valid( $value )
    {
        Assertion::notEmpty( $value );
        Assertion::string( $value );

        if( in_array( $value, array_keys( $this->get_potential_options() ) ) )
        {
            return false;
        }

        return true;
    }

    public function get_html( )
    {
        switch( $this->get_option_type() )
        {
            case self::OPTION_TYPE_RADIO:
                return $this->_render_as_radio_buttons();

            case self::OPTION_TYPE_CHECKBOX:
                return $this->_render_as_checkboxes();

            default:
                throw new \Exception( 'Unknown option type: ' . esc_html( $this->get_option_type() ) );
        }
    }

    public function _render_as_checkboxes()
    {
        $current_value = $this->_secretary->get_option_value( $this );

        $ret = '';
        $ret .= sprintf( '<label for="%1$s">', $this->get_storage_name() );
        $ret .= esc_html( $this->get_description() );

        $checked = '';
        if( $current_value === $this->get_true_value() )
        {
            $checked = ' checked="checked"';
        }

        $ret .= sprintf( '<input type="checkbox" name="%1$s" id="%1$s" value="true" %2$s />', $this->get_storage_name(), $checked );

        $ret .= '</label>';

        return $ret;
    }

    public function _render_as_radio_buttons()
    {
        $ret = '';

        $ret .= sprintf( '<h2>%1$s</h2>', esc_html( $this->get_description() ) );
        $current_value = $this->_secretary->get_option_value( $this );
        foreach( $this->get_potential_options() as $value => $description )
        {
            $checked = '';
            if( $current_value === $value )
            {
                $checked = ' checked="checked"';
            }
            $ret .= sprintf( '<label for="%2$s-%1$s">', $value, $this->get_storage_name() );
            $ret .= sprintf( '<input type="radio" name="%2$s" id="%2$s-%1$s" value="%1$s" %3$s />', $value, $this->get_storage_name(), $checked );
            $ret .= esc_html( $description );
            $ret .= '</label>';
            $ret .= '<br />';
        }

        return $ret;
    }
}