<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\Secretary;

final class non_global_constant_secretary extends Secretary
{
    private $_CONSTANTS = array();

    private $_FUNCTIONS = array();

    public function reset_all()
    {
        $this->_CONSTANTS = array();
        $this->_FUNCTIONS = array();
    }

    public function set_constant( $name, $value )
    {
        $this->_CONSTANTS[ $name ] = $value;
    }

    public function unset_constant( $name )
    {
        unset( $this->_CONSTANTS[ $name ] );
    }

    public function is_constant_defined( $name )
    {
        return array_key_exists( $name, $this->_CONSTANTS );
    }

    public function get_constant_value( $name )
    {
        if( ! $this->is_constant_defined( $name ) )
        {
            throw new \Exception( 'Attempt at using constant before checking for definition' );
        }
        return $this->_CONSTANTS[ $name ];
    }
}
