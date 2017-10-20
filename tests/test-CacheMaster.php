<?php

namespace Vendi\Cache\Tests;

use League\Flysystem\Adapter\Local;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Vendi\Cache\CacheMaster;
use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

/**
 * We need to extend from \WP_UnitTestCase because we're calling WP core
 * functions and we need cleanup of stuff.
 */
class test_CacheMaster extends vendi_cache_test_base
{

    private function _get_obj( Maestro $maestro = null )
    {
        if( null === $maestro )
        {
            $maestro = $this->__get_new_maestro();
        }
        return new CacheMaster( $maestro );
    }

    private function _get_obj_with_custom_secretary()
    {
        return new CacheMaster( $this->__get_new_maestro() );
    }

    private function _get_obj_with_custom_filesystem( $dir )
    {
        return $this->_get_obj( $this->__get_new_maestro( null, null, $dir ) );
    }

    /**
     * @covers Vendi\Cache\CacheMaster::__construct
     */
    public function test___construct()
    {
        $this->assertInstanceOf(
                                    '\\Vendi\\Cache\\Maestro',
                                    $this->_get_obj()->get_maestro()
                            );
    }

    /**
     * @dataProvider provider_for_test__get_XYZ_no_gen
     * @covers Vendi\Cache\CacheMaster::get_updater()
     * @covers Vendi\Cache\CacheMaster::get_cache_key_generator()
     */
    public function test__get_XYZ_no_gen( $property, $method, $type )
    {
        $cache_master = $this->_get_obj();
        $this->assertInstanceOf( $type, $cache_master->$method( ) );

        $this->setExpectedException( '\Exception', "The property $property is null and the getter $method was requested to not generate a new one." );
        $cache_master = $this->_get_obj();
        $cache_master->$method( true );
    }

    /**
     * @dataProvider provider_for_test__get_XYZ__passthrough
     * @covers Vendi\Cache\CacheMaster::get_maestro()
     * @covers Vendi\Cache\CacheMaster::get_logger()
     * @covers Vendi\Cache\CacheMaster::get_secretary()
     * @covers Vendi\Cache\CacheMaster::get_file_system()
     */
    public function test__get_XYZ__passthrough( $method, $type )
    {
        $this->assertInstanceOf( $type, $this->_get_obj()->$method( ) );
    }

    /**
     * @covers Vendi\Cache\CacheMaster::is_resource_not_cacheable()
     */
    public function test_is_resource_not_cacheable()
    {
        //No one should be logged in by default
        $cache_master = $this->_get_obj_with_custom_secretary();
        $this->assertFalse( $cache_master->is_resource_not_cacheable() );

        wp_set_current_user( 1 );
        $cache_master = $this->_get_obj_with_custom_secretary();
        $this->assertTrue( $cache_master->is_resource_not_cacheable() );
    }

    /**
     * @covers Vendi\Cache\CacheMaster::is_user_logged_in()
     */
    public function test_is_user_logged_in()
    {
        //No one should be logged in by default
        $cache_master = $this->_get_obj();
        $this->assertFalse( $cache_master->is_user_logged_in() );

        //Log a user in
        wp_set_current_user( 1 );
        $cache_master = $this->_get_obj();
        $this->assertTrue( $cache_master->is_user_logged_in() );
    }

    /**
     * @covers Vendi\Cache\CacheMaster::file_exists()
     * @covers Vendi\Cache\CacheMaster::write_file()
     * @covers Vendi\Cache\CacheMaster::delete_file()
     */
    public function test__file_io()
    {
        $dir = $this->create_temp_dir();
        $this->assertTrue( is_dir( $dir ) );

        $obj = $this->_get_obj_with_custom_filesystem( $dir );

        $file = 'test/more-test';
        $contents = 'cheese';

        $abs_path = \Webmozart\PathUtil\Path::join( $dir, $file );

        $this->assertFalse( $obj->file_exists( $file ) );
        $this->assertTrue( $obj->write_file( $file, $contents ) );
        $this->assertTrue( $obj->file_exists( $file ) );
        $this->assertTrue( file_exists( $abs_path ) );
        $this->assertTrue( $obj->delete_file( $file ) );
        $this->assertTrue( $obj->delete_dir( 'test/' ) );
    }

    /**
     * @covers Vendi\Cache\CacheMaster::delete_cache_dir_contents()
     * @covers Vendi\Cache\CacheMaster::write_file()
     */
    public function test__delete_cache_dir_contents()
    {
        $dir = $this->create_temp_dir();
        $this->assertTrue( is_dir( $dir ) );

        $obj = $this->_get_obj_with_custom_filesystem( $dir );

        $files = [
                    'test/alpha/beta.txt',
                    'test/cheese/example.txt',
                ];

        $contents = 'cheese';

        foreach( $files as $file )
        {
            $this->assertTrue( $obj->write_file( $file, $contents ) );
            $this->_files[] = \Webmozart\PathUtil\Path::join( $dir, $file );
        }

        $this->assertTrue( $obj->delete_cache_dir_contents( $dir ) );
    }

    public function provider_for_test__get_XYZ__passthrough()
    {
        return [
                    [ 'get_maestro',     '\\Vendi\\Cache\\Maestro' ],
                    [ 'get_logger',      '\\Psr\Log\LoggerInterface' ],
                    [ 'get_secretary',   '\\Vendi\\Cache\\Secretary' ],
                    [ 'get_file_system', '\\League\Flysystem\Filesystem' ],
            ];
    }

    public function provider_for_test__get_XYZ_no_gen()
    {
        return [
                    [ '_updater',             'get_updater',             'Vendi\\Cache\\PluginUpdater' ],
                    [ '_cache_key_generator', 'get_cache_key_generator', 'Vendi\\Cache\\CacheKeyGenerator' ],
            ];
    }
}
