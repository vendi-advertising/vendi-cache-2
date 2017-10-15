<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

class test_Secretary extends \PHPUnit_Framework_TestCase
{
    /**
     * PHPUnit 6+ compatibility shim.
     *
     * @see https://github.com/WordPress/wordpress-develop/blob/master/tests/phpunit/includes/testcase.php#L446
     *
     * @param mixed      $exception
     * @param string     $message
     * @param int|string $code
     */
    public function setExpectedException( $exception, $message = '', $code = null )
    {
        if ( method_exists( 'PHPUnit_Framework_TestCase', 'setExpectedException' ) )
        {
            parent::setExpectedException( $exception, $message, $code );
        }
        else
        {
            $this->expectException( $exception );
            if ( '' !== $message )
            {
                $this->expectExceptionMessage( $message );
            }
            if ( null !== $code )
            {
                $this->expectExceptionCode( $code );
            }
        }
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
     * @covers Vendi\Cache\Secretary::is_function_defined
     */
    public function test_is_function_defined()
    {
        $secretary = Maestro::get_default_instance()
                            ->get_secretary()
                    ;

        $this->assertFalse( $secretary->is_function_defined( 'global_function_must_only_ever_be_included_once' ) );

        //Include a file that creates a function in the global namespace.
        //Anyone know of another way to do this?
        include VENDI_CACHE_DIR . '/tests/includes/global_function_must_only_ever_be_included_once.php';
        $this->assertTrue( $secretary->is_function_defined( 'global_function_must_only_ever_be_included_once' ) );
        $this->assertSame( 'CHEESE', $secretary->get_function_value( 'global_function_must_only_ever_be_included_once' ) );
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
}
