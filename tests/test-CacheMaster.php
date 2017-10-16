<?php

namespace Vendi\Cache\Tests;

use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\CacheMaster;
use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

class test_CacheMaster extends vendi_cache_test_base
{
    private function _get_obj( Maestro $maestro = null )
    {
        if( null === $maestro )
        {
            $maestro = new Maestro();
        }
        return new CacheMaster( $maestro );
    }

    private function _get_obj_with_custom_secretary()
    {
        $maestro = ( new Maestro() )
                    ->with_secretary( new non_global_constant_secretary() )
                ;

        return new CacheMaster( $maestro );
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
     * @covers Vendi\Cache\CacheMaster::is_user_logged_in()
     */
    public function test__is_user_logged_in()
    {
        $cache_master = $this->_get_obj_with_custom_secretary();

        //This global function should not exist
        $this->assertFalse( $cache_master->get_secretary()->is_function_defined( 'wp_get_current_user' ) );
        $this->assertFalse( $cache_master->is_user_logged_in() );

        //Define the function but return an invalid value
        $cache_master->get_secretary()->set_function(
                                                        'wp_get_current_user',
                                                        function()
                                                        {
                                                            false;
                                                        }
                                                    );
        $this->assertFalse( $cache_master->is_user_logged_in() );

        //Re-define the function, return a valid value but without an ID
        $cache_master->get_secretary()->set_function(
                                                        'wp_get_current_user',
                                                        function()
                                                        {
                                                            return new \WP_User();
                                                        }
                                                    );
        $this->assertFalse( $cache_master->is_user_logged_in() );

        //Finally, return a user with an ID actually set which WP considers to be
        //a valid user.
        $cache_master->get_secretary()->set_function(
                                                        'wp_get_current_user',
                                                        function()
                                                        {
                                                            $ret = new \WP_User();
                                                            $ret->ID = 1;
                                                            return $ret;
                                                        }
                                                    );
        $this->assertTrue( $cache_master->is_user_logged_in() );
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
