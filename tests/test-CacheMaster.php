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

        $cache_master = $this->_get_obj();
        //TODO: MAGIC NUMBER!!!!
        $this->assertTrue($cache_master->_should_output_buffer_handling_continue(\str_repeat('a', 1000)));
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

    /**
     * @covers \Vendi\Cache\CacheMaster::_get_output_buffer_debug_messages_as_tuple()
     */
    public function test__get_output_buffer_debug_messages_as_tuple()
    {
        $cache_master = $this->_get_obj();
        $result = $cache_master->_get_output_buffer_debug_messages_as_tuple(\str_repeat('a', 5));
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('append', $result);
        $this->assertArrayHasKey('appendGzip', $result);

        //TODO: Look at debug message
    }

    /**
     * @covers \Vendi\Cache\CacheMaster::handle_ob_complete()
     */
    public function test_handle_ob_complete()
    {
        $cache_master = $this->_get_obj();

        //Init VFS
        $this->get_vfs_root();

        $result = $cache_master->handle_ob_complete(\str_repeat('a', 1000));
        $this->assertInternalType('string', $result);
    }

    /**
     * @covers \Vendi\Cache\CacheMaster::handle_ob_complete()
     */
    public function test_handle_ob_complete__return_false()
    {
        $this->assertFalse($this->_get_obj()->handle_ob_complete(''));
    }

    /**
     * @covers \Vendi\Cache\CacheMaster::is_https_page()
     */
    public function test_is_https_page()
    {
        $maestro = $this->__get_new_maestro($this->__create_server_request_from_url('https://www.example.com'));
        $this->assertTrue($this->_get_obj($maestro)->is_https_page());

        $maestro = $this->__get_new_maestro($this->__create_server_request_from_url('http://www.example.com'));
        $this->assertFalse($this->_get_obj($maestro)->is_https_page());
    }

    /**
     * @covers \Vendi\Cache\CacheMaster::_flag_request_as_cacheable()
     * @covers \Vendi\Cache\CacheMaster::_flag_request_as_not_cacheable()
     * @covers \Vendi\Cache\CacheMaster::_get_resource_not_cacheable_flag()
     */
    public function test__flag_request_as_cacheable()
    {
        $cache_master = $this->_get_obj();
        $this->assertNull($cache_master->_get_resource_not_cacheable_flag());
        $cache_master->_flag_request_as_cacheable();
        $this->assertFalse($cache_master->_get_resource_not_cacheable_flag());
        $cache_master->_flag_request_as_not_cacheable();
        $this->assertTrue($cache_master->_get_resource_not_cacheable_flag());

    }

    /**
     * @covers \Vendi\Cache\CacheMaster::_set_ajax_only_hooks()
     */
    public function test__set_ajax_only_hooks()
    {
        $cache_master = $this->_get_obj();
        $this->assertFalse(\has_action(CacheMaster::ACTION_NAME__CACHE_CLEAR));
        $cache_master->_set_ajax_only_hooks();
        $this->assertTrue(\has_action(CacheMaster::ACTION_NAME__CACHE_CLEAR));
    }

    /**
     * @covers \Vendi\Cache\CacheMaster::_setup_main_hooks()
     */
    public function test__setup_main_hooks()
    {
        //Actions and known callbacks declared by base class
        //TODO: Really, we should either just move this to a non-WP test class
        //or just remove all actions during this run.
        $actions = [
                        'publish_post' => [ [0, '_delete_option_fresh_site'], [5, '_publish_post_hook'] ],
                        'publish_page' => [ [0, '_delete_option_fresh_site']],
                        'clean_object_term_cache',
                        'clean_post_cache',
                        'clean_term_cache',
                        'clean_page_cache',
                        'after_switch_theme' => [ [10, '_wp_menus_changed'], [10, '_wp_sidebars_changed'] ],
                        'customize_save_after' => [[ 0, '_delete_option_fresh_site']],
                        'activated_plugin',
                        'deactivated_plugin',
                        'update_option_sidebars_widgets',
                        'comment_post' => [[10, 'wp_new_comment_notify_moderator'], [10, 'wp_new_comment_notify_postauthor']],
        ];
        $filters = [
                        'wp_redirect',
        ];

        foreach($actions as $hook => $funcs){
            if(is_array($funcs)){
                foreach($funcs as $f){
                    \remove_action($hook, $f[1], $f[0]);
                }
            }else{
                $hook = $funcs;
            }
            $this->assertFalse(\has_action($hook));
        }
        foreach($filters as $hook){
            $this->assertFalse(\has_filter($hook));
        }

        $cache_master = $this->_get_obj();
        wp_set_current_user(1);
        $cache_master->_setup_main_hooks();


        foreach($actions as $hook => $funcs){
            if(!is_array($funcs)){
                $hook = $funcs;
            }
            $this->assertTrue(\has_action($hook));
        }
        foreach($filters as $hook){
            $this->assertTrue(\has_filter($hook));
        }
    }

    /**
     * @covers \Vendi\Cache\CacheMaster::_setup_main_hooks()
     */
    public function test__setup_main_hooks__called_twice()
    {
        $cache_master = $this->_get_obj();
        $cache_master->_setup_main_hooks();
        $cache_master->_setup_main_hooks();
        $this->assertSameLastMessage('Caching hooks already setup');
    }

    /*
    TODO:
    _setup_main_hooks
    _maybe_purge_cached_file_on_non_GET_method
    _maybe_serve_cached_file
    _setup_actual_request_caching
    _write_output_buffer_to_disk
    setup_caching
    handle_action_comment_post
    handle_filter_redirect_filter
    handle_action_publish_post
    delete_file_from_permalink
    clear_entire_page_cache
    schedule_cache_clear
    handle_action_clear_page_cache
     */

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
