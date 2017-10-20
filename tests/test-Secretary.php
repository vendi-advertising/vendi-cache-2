<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

class test_Secretary extends vendi_cache_test_base
{

    /**
     * @covers Vendi\Cache\Secretary::get_network_option
     * @covers Vendi\Cache\Secretary::set_network_option
     */
    public function test_network_option()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();

        $this->assertFalse( $secretary->get_network_option( 'CHEESE' ) );
        $secretary->set_network_option( 'CHEESE', 'GLORP' );
        $this->assertSame( 'GLORP',  $secretary->get_network_option( 'CHEESE' ) );

    }

    /**
     * @covers Vendi\Cache\Secretary::is_constant_defined
     * @covers Vendi\Cache\Secretary::get_constant_value
     */
    public function test_constants()
    {
        //We're testing actual constants here so we need the default Secretary
        $maestro = $this->__get_new_maestro( null, null, null, Maestro::get_default_secretary() );
        $secretary = $maestro->get_secretary();

        $this->assertFalse( $secretary->is_constant_defined( 'CHEESE' ) );
        define( 'CHEESE', 'GLORP' );
        $this->assertTrue( $secretary->is_constant_defined( 'CHEESE' ) );
        $this->assertSame( 'GLORP',  $secretary->get_constant_value( 'CHEESE' ) );

    }

    /**
     * @covers Vendi\Cache\Secretary::get_cache_folder_abs
     */
    public function test_get_cache_folder_abs()
    {
        // $dir = $this->create_temp_dir();

        $secretary = $this->__get_new_maestro()->get_secretary();

        $this->assertFalse( $secretary->is_constant_defined( 'VENDI_CACHE_FOLDER_ABS' ) );
    }
}
