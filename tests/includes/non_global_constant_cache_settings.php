<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\DefaultSettings;

final class non_global_constant_cache_settings extends DefaultSettings
{
    private $_CONSTANTS = [];

    private $_FUNCTIONS = [];

    public function reset_all()
    {
        $this->_CONSTANTS = [];
        $this->_FUNCTIONS = [];
    }

    public function set_constant( $name, $value )
    {
        $this->_CONSTANTS[ $name ] = $value;
    }

    public function unset_constant( $name )
    {
        unset( $this->_CONSTANTS[ $name ] );
    }

    public function set_function( $name, callable $func )
    {
        $this->_FUNCTIONS[ $name ] = $func;
    }

    public function unset_function( $name )
    {
        unset( $this->_FUNCTIONS[ $name ] );
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

    public function is_function_defined( $name )
    {
        return array_key_exists( $name, $this->_FUNCTIONS );
    }

    public function get_function_value( $name )
    {
        if( ! $this->is_function_defined( $name ) )
        {
            throw new \Exception( 'Attempt at using function before checking for definition' );
        }

        $func = $this->_FUNCTIONS[ $name ];

        $args = func_get_args();

        //First $args is actually the $name variable above
        switch( count( $args ) )
        {
            case 1:
                return $func();

            case 2:
                return $func( $args[ 1 ] );

            case 3:
                return $func( $args[ 1 ], $args[ 2 ] );

            case 4:
                return $func( $args[ 1 ], $args[ 2 ], $args[ 3 ] );

            case 5:
                return $func( $args[ 1 ], $args[ 2 ], $args[ 3 ], $args[ 4 ] );
        }

        throw new \Exception( 'Custom get_function_value() only support a maximum of 4 arguments' );
    }
}
