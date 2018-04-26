<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheMaster;
use Vendi\Cache\Maestro;

/**
 * We need to extend from \WP_UnitTestCase because we're calling WP core
 * functions and we need cleanup of stuff.
 */
/**
 * @group WordPress
 */
class test_CacheMaster extends vendi_cache_test_base
{
    private function _get_obj(Maestro $maestro = null)
    {
        if (null === $maestro) {
            $maestro = $this->__get_new_maestro();
        }
        return new CacheMaster($maestro);
    }

    private function _get_obj_with_custom_secretary()
    {
        return new CacheMaster($this->__get_new_maestro());
    }

    /**
     * @covers \Vendi\Cache\CacheMaster::__construct
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
     * @covers \Vendi\Cache\CacheMaster::get_updater()
     * @covers \Vendi\Cache\CacheMaster::get_cache_key_generator()
     * @param mixed $property
     * @param mixed $method
     * @param mixed $type
     */
    public function test__get_XYZ_no_gen($property, $method, $type)
    {
        $cache_master = $this->_get_obj();
        $this->assertInstanceOf($type, $cache_master->$method());

        $this->setExpectedException('\Exception', "The property $property is null and the getter $method was requested to not generate a new one.");
        $cache_master = $this->_get_obj();
        $cache_master->$method(true);
    }

    /**
     * @dataProvider provider_for_test__get_XYZ__passthrough
     * @covers \Vendi\Cache\CacheMaster::get_maestro()
     * @covers \Vendi\Cache\CacheMaster::get_logger()
     * @covers \Vendi\Cache\CacheMaster::get_secretary()
     * @param mixed $method
     * @param mixed $type
     */
    public function test__get_XYZ__passthrough($method, $type)
    {
        $this->assertInstanceOf($type, $this->_get_obj()->$method());
    }

    /**
     * @covers \Vendi\Cache\CacheMaster::is_resource_not_cacheable()
     */
    public function test_is_resource_not_cacheable()
    {
        //No one should be logged in by default
        $cache_master = $this->_get_obj_with_custom_secretary();
        $this->assertFalse($cache_master->is_resource_not_cacheable());

        wp_set_current_user(1);
        $cache_master = $this->_get_obj_with_custom_secretary();
        $this->assertTrue($cache_master->is_resource_not_cacheable());
    }

    /**
     * @covers \Vendi\Cache\CacheMaster::is_user_logged_in()
     */
    public function test_is_user_logged_in()
    {
        //No one should be logged in by default
        $cache_master = $this->_get_obj();
        $this->assertFalse($cache_master->is_user_logged_in());

        //Log a user in
        wp_set_current_user(1);
        $cache_master = $this->_get_obj();
        $this->assertTrue($cache_master->is_user_logged_in());
    }

    public function provider_for_test__get_XYZ__passthrough()
    {
        return [
                    [ 'get_maestro',     '\\Vendi\\Cache\\Maestro' ],
                    [ 'get_logger',      '\\Psr\Log\LoggerInterface' ],
                    [ 'get_secretary',   '\\Vendi\\Cache\\Secretary' ],
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
