<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

class test_Maestro extends vendi_cache_test_base
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

    /**
     * @covers Vendi\Cache\Maestro::get_cache_master()
     */
    public function test_get_cache_master()
    {
        $this->assertInstanceOf( 'Vendi\\Cache\\CacheMaster', Maestro::get_default_instance()->get_cache_master( ) );
    }

    /**
     * @covers Vendi\Cache\Maestro::__get()
     */
    public function test___get()
    {
        $this->setExpectedException( '\Exception', 'Attempt at getting undeclared property xyz.' );

        $maestro = new Maestro();
        $maestro->xyz;
    }

    /**
     * @covers Vendi\Cache\Maestro::__set()
     */
    public function test___set()
    {
        $this->setExpectedException( '\Exception', 'Attempt at setting undeclared property xyz.' );

        $maestro = new Maestro();
        $maestro->xyz = 'ABC';
    }

    /**
     * @dataProvider provider_for_test__get_XYZ_no_gen
     * @covers Vendi\Cache\Maestro::get_admin_ui()
     * @covers Vendi\Cache\Maestro::get_request()
     * @covers Vendi\Cache\Maestro::get_logger()
     * @covers Vendi\Cache\Maestro::get_adapter()
     * @covers Vendi\Cache\Maestro::get_secretary()
     * @covers Vendi\Cache\Maestro::get_file_system()
     * @covers Vendi\Cache\Maestro::get_cache_master()
     */
    public function test__get_XYZ_no_gen( $property, $method, $type )
    {
        $maestro = new Maestro();
        $this->assertInstanceOf( $type, $maestro->$method( ) );

        $this->setExpectedException( '\Exception', "The property $property is null and the getter $method was requested to not generate a new one." );
        $maestro = new Maestro();
        $maestro->$method( true );
    }

    public function provider_for_test__get_XYZ_no_gen()
    {
        return [
                    [ '_admin_ui',     'get_admin_ui',     'Vendi\\Cache\\Admin\\UI' ],
                    [ '_request',      'get_request',      'Symfony\\Component\\HttpFoundation\\Request' ],
                    [ '_logger',       'get_logger',       'Psr\\Log\\LoggerInterface' ],
                    [ '_adapter',      'get_adapter',      'League\\Flysystem\\AdapterInterface' ],
                    [ '_secretary',    'get_secretary',    'Vendi\\Cache\\Secretary' ],
                    [ '_file_system',  'get_file_system',  'League\\Flysystem\\Filesystem' ],
                    [ '_cache_master', 'get_cache_master', 'Vendi\\Cache\\CacheMaster' ],
            ];
    }
}
