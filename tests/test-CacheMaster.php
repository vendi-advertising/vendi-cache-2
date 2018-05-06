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
     * @covers \Vendi\Cache\CacheMaster::log_request_as_not_cacheable()
     */
    public function test_log_request_as_not_cacheable()
    {
        //No one should be logged in by default
        $cache_master = $this->_get_obj_with_custom_secretary();
        $cache_master->log_request_as_not_cacheable(['CHEESE' => 'GLORP']);
        $this->assertSameLastMessage('Request not cacheable', true);
        $this->assertCount(1, $this->_get_last_log()['context']);
        $this->assertSame('GLORP', $this->_get_last_log()['context']['CHEESE']);
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

    /**
     * @covers \Vendi\Cache\CacheMaster::_should_output_buffer_handling_continue()
     */
    public function test__should_output_buffer_handling_continue()
    {
        $cache_master = $this->_get_obj();
        $this->assertFalse($cache_master->get_secretary()->does_function_exist('\is_404'));
        $cache_master->get_secretary()->set_function('\is_404', function () {
            return true;
        });
        $this->assertTrue($cache_master->get_secretary()->does_function_exist('\is_404'));
        $this->assertFalse($cache_master->_should_output_buffer_handling_continue());


        $cache_master = $this->_get_obj();
        $this->assertFalse($cache_master->get_secretary()->is_constant_defined('VENDI_CACHE_PHP_ERROR'));
        $cache_master->get_secretary()->set_constant('VENDI_CACHE_PHP_ERROR', true);
        $this->assertTrue($cache_master->get_secretary()->is_constant_defined('VENDI_CACHE_PHP_ERROR'));
        $this->assertFalse($cache_master->_should_output_buffer_handling_continue());

        $cache_master = $this->_get_obj();
        //TODO: MAGIC NUMBER!!!!
        $this->assertFalse($cache_master->_should_output_buffer_handling_continue(\str_repeat('a', 999)));
    }

    /**
     * @covers \Vendi\Cache\CacheMaster::_should_output_buffer_handling_continue()
     */
    public function test__should_output_buffer_handling_continue__legacy_filter()
    {
        $cache_master = $this->_get_obj();
        $this->assertFalse(\has_filter(CacheMaster::LEGACY_FILTER_NAME__NO_CACHE));
        \add_filter(CacheMaster::LEGACY_FILTER_NAME__NO_CACHE, function () {
            return true;
        });
        $this->assertTrue(\has_filter(CacheMaster::LEGACY_FILTER_NAME__NO_CACHE));
        $this->assertFalse($cache_master->_should_output_buffer_handling_continue());
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
