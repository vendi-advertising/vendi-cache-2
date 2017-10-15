<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

class test_Maestro extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Vendi\Cache\Maestro::__construct()
     */
    public function test___construct()
    {
        $maestro = new Maestro();
        $this->assertInstanceOf( 'Vendi\\Cache\\Admin\\UI', $maestro->get_admin_ui() );
    }

    /**
     * @covers Vendi\Cache\Maestro::with_admin_ui()
     */
    public function test_with_admin_ui()
    {
        $maestro = ( new Maestro() );

        $this->assertInstanceOf( 'Vendi\\Cache\\Maestro', $maestro->with_admin_ui( Maestro::get_default_admin_ui( $maestro ) ) );
    }

    /**
     * @covers Vendi\Cache\Maestro::with_request()
     */
    public function test_with_request()
    {
        $maestro = ( new Maestro() );

        $this->assertInstanceOf( 'Vendi\\Cache\\Maestro', $maestro->with_request( Maestro::get_default_request( ) ) );
    }

    /**
     * @covers Vendi\Cache\Maestro::with_secretary()
     */
    public function test_with_secretary()
    {
        $maestro = ( new Maestro() );

        $this->assertInstanceOf( 'Vendi\\Cache\\Maestro', $maestro->with_secretary( Maestro::get_default_secretary( ) ) );
    }

    /**
     * @covers Vendi\Cache\Maestro::with_logger()
     */
    public function test_with_logger()
    {
        $maestro = ( new Maestro() );

        $this->assertInstanceOf( 'Vendi\\Cache\\Maestro', $maestro->with_logger( Maestro::get_default_logger( Maestro::get_default_secretary( ) ) ) );
    }

    /**
     * @covers Vendi\Cache\Maestro::with_file_system_adapter()
     */
    public function test_with_file_system_adapter()
    {
        $maestro = ( new Maestro() );

        $this->assertInstanceOf( 'Vendi\\Cache\\Maestro', $maestro->with_file_system_adapter( Maestro::get_default_adapter( Maestro::get_default_secretary( ) ) ) );
    }

    /**
     * @covers Vendi\Cache\Maestro::get_default_admin_ui()
     */
    public function test_get_default_admin_ui()
    {
        $this->assertInstanceOf( 'Vendi\\Cache\\Admin\\UI', Maestro::get_default_admin_ui( new Maestro() ) );
    }

    /**
     * @covers Vendi\Cache\Maestro::get_default_request()
     */
    public function test_get_default_request()
    {
        $this->assertInstanceOf( 'Symfony\\Component\\HttpFoundation\\Request', Maestro::get_default_request( ) );
    }
}



