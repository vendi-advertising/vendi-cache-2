<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

class test_Secretary extends vendi_cache_test_base
{
    private $_dirs = array();

    private $_files = array();

    public function tearDown()
    {
        parent::tearDown();

        foreach( $this->_files as $f )
        {
            if( is_file( $f ) )
            {
                unlink( $f );
            }
        }

        foreach( $this->_dirs as $d )
        {
            if( is_dir( $d ) )
            {
                rmdir( $d );
            }
        }
    }

    //https://stackoverflow.com/a/1707859/231316
    private function _create_temp_dir()
    {
        $tempfile = tempnam( sys_get_temp_dir(), 'VC2' );
        if( false === $tempfile )
        {
            throw new \Exception( 'Could not create file for temporary directory' );
        }

        if( file_exists( $tempfile ) )
        {
            unlink( $tempfile );
        }

        mkdir( $tempfile );

        if( ! is_dir( $tempfile ) )
        {
            throw new \Exception( 'Could not create temporary directory' );
        }

        $this->_dirs[] = $tempfile;

        return $tempfile;
    }

    private function _get_obj()
    {
        return new non_global_constant_secretary( new Maestro() );
    }

    private function _get_maestro( $dir )
    {
        // _get_new_maestro
    }

    /**
     * @covers Vendi\Cache\Secretary::get_network_option
     * @covers Vendi\Cache\Secretary::set_network_option
     */
    public function test_network_option()
    {
        $secretary = Maestro::get_default_instance()
                            ->get_secretary()
                    ;

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
        $secretary = Maestro::get_default_instance()
                            ->get_secretary()
                    ;

        $this->assertFalse( $secretary->is_constant_defined( 'CHEESE' ) );
        define( 'CHEESE', 'GLORP' );
        $this->assertTrue( $secretary->is_constant_defined( 'CHEESE' ) );
        $this->assertSame( 'GLORP',  $secretary->get_constant_value( 'CHEESE' ) );

    }

    /**
     * @covers Vendi\Cache\Secretary::get_function_value
     */
    public function test_get_function_value()
    {
        $secretary = Maestro::get_default_instance()
                            ->get_secretary()
                    ;

        $this->assertSame(
                            'ZERO',
                            $secretary->get_function_value(
                                                            function()
                                                            {
                                                                return 'ZERO';
                                                            }
                                                        )
                        );

        $this->assertSame(
                            'ONE',
                            $secretary->get_function_value(
                                                            function()
                                                            {
                                                                $args = func_get_args();
                                                                return end( $args );
                                                            },
                                                            'ONE'
                                                        )
                        );

        $this->assertSame(
                            'TWO',
                            $secretary->get_function_value(
                                                            function()
                                                            {
                                                                $args = func_get_args();
                                                                return end( $args );
                                                            },
                                                            'ONE',
                                                            'TWO'
                                                        )
                        );

        $this->assertSame(
                            'THREE',
                            $secretary->get_function_value(
                                                            function()
                                                            {
                                                                $args = func_get_args();
                                                                return end( $args );
                                                            },
                                                            'ONE',
                                                            'TWO',
                                                            'THREE'
                                                        )
                        );

        $this->assertSame(
                            'FOUR',
                            $secretary->get_function_value(
                                                            function()
                                                            {
                                                                $args = func_get_args();
                                                                return end( $args );
                                                            },
                                                            'ONE',
                                                            'TWO',
                                                            'THREE',
                                                            'FOUR'
                                                        )
                        );
    }

    /**
     * @covers Vendi\Cache\Secretary::get_function_value
     */
    public function test_get_function_value__too_many_arguments()
    {

        $this->setExpectedException( '\Exception', 'Custom get_function_value() only support a maximum of 4 arguments' );

        Maestro::get_default_instance()
                ->get_secretary()
                ->get_function_value(
                            function(){},
                            'ONE',
                            'TWO',
                            'THREE',
                            'FOUR',
                            'FIVE'
            );
    }

    /**
     * @covers Vendi\Cache\Secretary::get_cache_folder_abs
     */
    public function test_get_cache_folder_abs()
    {
        $dir = $this->_create_temp_dir();

        $secretary = $this->_get_obj();

        $this->assertFalse( $secretary->is_constant_defined( 'VENDI_CACHE_FOLDER_ABS' ) );
    }
}
