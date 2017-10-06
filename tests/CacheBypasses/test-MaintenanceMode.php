<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\MaintenanceMode;
use Webmozart\PathUtil\Path;

class test_MaintenanceMode extends cache_bypass_base
{
    /**
     * @covers Vendi\Cache\CacheBypasses\MaintenanceMode::is_cacheable
     */
    public function test_is_cacheable__magic_file( )
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $test = new MaintenanceMode( $maestro );

        //This will be false because ABSPATH isn't defined in the test suite yet
        $this->assertSame( false, $test->is_cacheable() );

        //Constant doesn't exist yet
        $this->assertFalse( $cache_settings->is_constant_defined( 'ABSPATH' ) );

        //Create a temporary folder to simulate ABSPATH and set global constant
        $dir = $this->create_temp_dir();
        $cache_settings->set_constant( 'ABSPATH', $dir );

        //Magic WP file path
        $path = Path::join( $dir, '.maintenance' );

        //Create the maintenance file
        $this->touch_file( $path );

        //Make sure it exists
        $this->assertFileExists( $path );

        //Caching should be disabled
        $this->assertSame( false, $test->is_cacheable() );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\MaintenanceMode::is_cacheable
     */
    public function test_is_cacheable__filters( )
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_cache_settings();

        $test = new MaintenanceMode( $maestro );

        //Create a temporary folder to simulate ABSPATH and set global constant
        $dir = $this->create_temp_dir();
        $cache_settings->set_constant( 'ABSPATH', $dir );

        $this->assertTrue( $test->is_cacheable() );

        remove_all_filters( 'enable_maintenance_mode' );
        add_filter( 'enable_maintenance_mode', function(){ return true; } );
        $this->assertFalse( $test->is_cacheable() );

        remove_all_filters( 'enable_maintenance_mode' );
        add_filter( 'enable_maintenance_mode', function(){ return false; } );
        $this->assertTrue( $test->is_cacheable() );

        remove_all_filters( 'enable_maintenance_mode' );

        $this->assertTrue( $test->is_cacheable() );
    }
}