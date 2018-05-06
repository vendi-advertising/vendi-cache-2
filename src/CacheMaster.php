<?php declare(strict_types=1);
namespace Vendi\Cache;

use Psr\Log\LoggerInterface;

final class CacheMaster extends AbstractMaestroEnabledBase
{
    //Set to true after all hooks are setup
    private $_caching_hooks_setup = false;

    private $_was_cache_clear_schedule_yet = false;

    const ACTION_NAME__CACHE_CLEAR = 'vendi/cache/clear';

    const LEGACY_FILTER_NAME__NO_CACHE = 'vendi-cache/do-not-cache';

    private $_is_resource_not_cacheable = null;

    private $_cache_key_generator = null;

    private $_updater = null;

    /**
     * [get_updater description].
     * @param  mixed            $do_not_create_new
     * @return UpdaterInterface
     */
    public function get_updater($do_not_create_new = false)
    {
        if (! $this->_updater instanceof PluginUpdater) {
            if ($do_not_create_new) {
                throw new \Exception(\sprintf(__('The property %1$s is null and the getter %2$s was requested to not generate a new one.', 'vendi-cache'), '_updater', 'get_updater'));
            }
            $this->_updater = new PluginUpdater($this->get_maestro());
        }

        return $this->_updater;
    }

    public function get_cache_key_generator($do_not_create_new = false)
    {
        if (! $this->_cache_key_generator instanceof CacheKeyGenerator) {
            if ($do_not_create_new) {
                throw new \Exception(\sprintf(__('The property %1$s is null and the getter %2$s was requested to not generate a new one.', 'vendi-cache'), '_cache_key_generator', 'get_cache_key_generator'));
            }
            $this->_cache_key_generator = new CacheKeyGenerator($this->get_maestro());
        }

        return $this->_cache_key_generator;
    }

    /**
     * [get_logger description].
     * @return LoggerInterface
     */
    public function get_logger()
    {
        return $this->get_maestro()->get_logger();
    }

    /**
     * [get_secretary description].
     * @return Secretary
     */
    public function get_secretary()
    {
        return $this->get_maestro()->get_secretary();
    }

    public function log_request_as_not_cacheable(array $args)
    {
        $this
            ->get_logger()
            ->info(__('Request not cacheable', 'vendi-cache'), $args)
        ;
    }

    /**
     * Checks if the current visitor is a logged in user.
     *
     * This function does not exist at cache setup time so it has been
     * duplicated here.
     *
     * @return bool true if user is logged in, false if not logged in
     */
    public function is_user_logged_in()
    {
        $user = wp_get_current_user();
        return $user->exists();
    }

    public function _set_ajax_only_hooks()
    {
        $this->get_logger()->debug('AJAX request found, only listening for cache clear');
        add_action(self::ACTION_NAME__CACHE_CLEAR, [ $this, 'clear_entire_page_cache' ]);
    }

    public function _setup_main_hooks()
    {
        $this->get_logger()->debug('Setting up caching hooks');

        if ($this->_caching_hooks_setup) {
            $this->get_logger()->warning('Caching hooks already setup');
            return;
        }

        add_action(self::ACTION_NAME__CACHE_CLEAR, [ $this, 'clear_entire_page_cache' ]);

        //In theory this should be put at the bottom
        $this->_caching_hooks_setup = true;

        if ($this->is_user_logged_in()) {
            $this->get_logger()->debug('User is logged in, additional hooks added');
            add_action('publish_post', [ $this, 'handle_action_publish_post' ]);
            add_action('publish_page', [ $this, 'handle_action_publish_post' ]);

            $hooks = [
                        'clean_object_term_cache',
                        'clean_post_cache',
                        'clean_term_cache',
                        'clean_page_cache',
                        'after_switch_theme',
                        'customize_save_after',
                        'activated_plugin',
                        'deactivated_plugin',
                        'update_option_sidebars_widgets',
                    ];

            foreach ($hooks as $action) {
                //Schedules a cache clear for immediately so it won't lag current request.
                add_action($action, [ $this, 'handle_action_clear_page_cache' ]);
            }

            $request = $this->get_maestro()->get_request();

            if ('POST' === $request->getMethod()) {
                $pages = [
                            '/wp-admin/options.php',
                            '/wp-admin/options-permalink.php',
                        ];

                $current_page = \mb_strtolower($request->getUri()->getPath());

                foreach ($pages as $page) {
                    if ($page === $current_page) {
                        $this->get_logger()->debug('POST to special admin page, clearing cache', [ 'page' => $page ]);
                        $this->schedule_cache_clear();
                        break;
                    }
                }
            }
        }

        //Might not be logged in
        add_action('comment_post', [ $this, 'handle_action_comment_post' ]);
        add_filter('wp_redirect', [ $this, 'handle_filter_redirect_filter' ]);
    }

    public function _maybe_purge_cached_file_on_non_GET_method()
    {
        $request = $this->get_maestro()->get_request();

        if ('GET' === $request->getMethod()) {
            return;
        }

        $cache_file = $this->get_cache_key_generator()->local_cache_filename_from_url();

        if ($this->get_maestro()->get_file_system()->file_exists($cache_file)) {
            $this->get_logger()->info(
                                        'Non-GET request received, evicting cache file',
                                        [
                                            'cache_file' => $cache_file,
                                            'method' => $request->getMethod()
                                        ]
                                    );
        }
    }

    public function is_resource_not_cacheable()
    {
        $test_runner = new CacheBypassTester($this->get_maestro());
        if ($test_runner->is_resource_not_cacheable()) {
            return true;
        }

        return false;
    }

    public function _flag_request_as_cacheable()
    {
        if ($this->_is_resource_not_cacheable) {
            $this->get_logger()->error('Request previously marked as not cacheable, cannot undo action');
            return;
        }

        $this->_is_resource_not_cacheable = false;
    }

    public function _flag_request_as_not_cacheable()
    {
        $this->_is_resource_not_cacheable = true;
    }

    public function _maybe_serve_cached_file()
    {
        if ($this->is_resource_not_cacheable()) {
            $this->_flag_request_as_not_cacheable();
            return;
        }

        $this->get_logger()->debug('Request marked as cacheable');
        $this->_flag_request_as_cacheable();

        $cache_file = $this->get_cache_key_generator()->local_cache_filename_from_url();


        if (! $this->get_maestro()->get_file_system()->file_exists($cache_file)) {
            $this->get_logger()->debug('Cache file not found', [ 'cache_file' => $cache_file ]);
            return;
        }

        $this->get_logger()->debug('Cache file found', [ 'cache_file' => $cache_file ]);

        $stat = @\stat($cache_file);
        if (! $stat) {
            $this->get_logger()->warning('Could not get file stats', [ 'cache_file' => $cache_file ]);
            return;
        }

        $age = \time() - $stat[ 'mtime' ];
        if ($age >= $this->get_secretary()->get_max_file_age()) {
            //TODO: Should we delete the file?

            $this->get_logger()->debug(
                                                            'Stale cache file found, skipping',
                                                            [
                                                                'cache_file' => $cache_file,
                                                                'age' => $age,
                                                                'max_age' => $this->get_secretary()->get_max_file_age(),
                                                            ]
                                                        );
            return;
        }

        $this->get_logger()->debug(
                                                        'Serving cache file',
                                                        [
                                                            'cache_file' => $cache_file,
                                                            'age' => $age,
                                                            'max_age' => $this->get_secretary()->get_max_file_age(),
                                                        ]
                                                    );

        //sends file to stdout
        \readfile($cache_file);

        //Terminate the request
        exit ;
    }

    public function _setup_actual_request_caching()
    {
        if ($this->_is_resource_not_cacheable) {
            $this->get_logger()->debug('Not setting up page caching, previous check flagged request as non-cacheable');
            return;
        }

        $this->get_logger()->debug('Setting up page caching');

        $maestro = $this->get_maestro();

        //Do not cache fatal errors
        global $vendi_cache_old_error_handler;
        $vendi_cache_old_error_handler = \set_error_handler(
                                                            function ($errno, $errstr, $errfile, $errline) use ($maestro) {
                                                                $eh = new ErrorHandler($maestro);
                                                                $eh->handle_error($errno, $errstr, $errfile, $errline);
                                                            }
            );

        global $vendi_cache_old_exception_handler;
        $vendi_cache_old_exception_handler = \set_exception_handler(
                                                            function ($exception) use ($maestro) {
                                                                $eh = new ErrorHandler($maestro);
                                                                $eh->handle_exception($exception);
                                                            }
         );

        \ob_start([ __CLASS__, 'handle_ob_complete' ]); //Setup routine to store the file
    }

    public function _should_output_buffer_handling_continue($buffer = '')
    {
        $secretary = $this->get_maestro()->get_secretary();

        if ($secretary->does_function_exist('\is_404') && $secretary->invoke_function('\is_404')) {
            $this->log_request_as_not_cacheable([ 'reason' => '404 detected' ]);
            return false;
        }

        if ($secretary->is_constant_defined('VENDI_CACHE_PHP_ERROR')) {
            $this->log_request_as_not_cacheable([ 'reason' => 'Explicit constant detected', 'constant' => 'VENDI_CACHE_PHP_ERROR' ]);
            return false;
        }

        if (\apply_filters(self::LEGACY_FILTER_NAME__NO_CACHE, false, $buffer)) {
            $this->log_request_as_not_cacheable([ 'reason' => 'Legacy filter return no cache', 'filter' => self::LEGACY_FILTER_NAME__NO_CACHE ]);
            return false;
        }

        //The average web page size is 1246,000 bytes. If web page is less than 1000 bytes, don't cache it.
        //TODO: Move to option
        if (\mb_strlen($buffer) < $this->get_secretary()->get_min_page_size()) {
            $this->log_request_as_not_cacheable([ 'reason' => 'Page too small', 'size' => \mb_strlen($buffer), 'min_size' => $this->get_secretary()->get_min_page_size() ]);
            return false;
        }
    }

    public function handle_ob_complete($buffer = '')
    {
        if (!$this->_should_output_buffer_handling_continue($buffer)) {
            return false;
        }

        $uri = $this->get_maestro()->get_request()->getUri();

        // $this->get_logger()->debug( 'Buffer', [ 'buffer' => strlen( $buffer ) ] );


        $cache_file = $this->get_cache_key_generator()->local_cache_filename_from_url();

        $append = "\n";
        // $appendGzip = "";
        // if( self::get_vwc_cache_settings()->get_do_append_debug_message() )
        // {
        $append = '<!-- Cached by Vendi Cache ';
        // if( self::get_vwc_cache_settings()->get_cache_mode() == cache_settings::CACHE_MODE_ENHANCED )
        // {
        $append .= 'Disk-Based Engine. ';
        // }
        // else
        // {
        //     $append .= 'PHP Caching Engine. ';
        // }
        //
        $append .= 'Time created on server: ' . \date('Y-m-d H:i:s T') . '. ';
        $append .= 'Protocol: ' . ($this->is_https_page() ? 'HTTPS' : 'HTTP') . '. ';
        $append .= 'Page size: ' . \mb_strlen($buffer) . ' bytes. ';

        $host = \wp_kses($uri->getHost(), []);

        $append .= 'Host: ' . $host . '. ';
        $append .= 'Request URI: ' . \wp_kses($uri->getPath(), []) . ' ';
        $appendGzip = $append . " Encoding: GZEncode -->\n";
        $append .= " Encoding: Uncompressed -->\n";
        // }

        $this->get_logger()->info('Caching file', [ 'cache_file' => $cache_file ]);
        $this->get_maestro()->get_file_system()->write_file($cache_file, $buffer . $append);
        // chmod( $file, 0644 );
        // if( self::$cacheType == cache_settings::CACHE_MODE_ENHANCED )
        // {
        //create gzipped files so we can send precompressed files
        $cache_file .= '_gzip';
        $this->get_logger()->info('Caching file gzip', [ 'cache_file' => $cache_file ]);
        $this->get_maestro()->get_file_system()->write_file($cache_file, \gzencode($buffer . $appendGzip, 9));
        // @file_put_contents( $file, gzencode( $buffer . $appendGzip, 9 ), LOCK_EX );
        // chmod( $file, 0644 );
        // }
        return $buffer;
    }

    /**
     * @return bool true if the reqeusted page was an HTTPS page, otherwise false
     */
    public function is_https_page()
    {
        //Prefer a core check since this is in flux right now
        if (is_ssl()) {
            return true;
        }

        return 'HTTPS' === \mb_strtoupper($this->get_maestro()->get_request()->getUri()->getScheme());
    }

    public function setup_caching()
    {
        if ($this->get_maestro()->get_secretary()->is_constant_defined('DOING_AJAX')) {
            $this->_set_ajax_only_hooks();
            return;
        }

        $this->_setup_main_hooks();

        $uri = $this->get_maestro()->get_request()->getUri();

        $this->get_logger()->debug(
                                                        'Request URL is:',
                                                        [
                                                            'host'   => $uri->getHost(),
                                                            'path'   => $uri->getPath(),
                                                            'secure' => $this->is_https_page(),
                                                        ]
                                                    );

        $this->_maybe_purge_cached_file_on_non_GET_method();

        $this->_maybe_serve_cached_file();

        $this->_setup_actual_request_caching();
    }

    public function handle_action_comment_post($comment_id)
    {
        $this->get_logger()->debug('Comment posted, scheduling cache clear', [ 'comment_id' => $comment_id ]);

        $c = \get_comment($comment_id, ARRAY_A);
        $perm = \get_permalink($c[ 'comment_post_ID' ]);
        $this->delete_file_from_permalink($perm);
        $this->schedule_cache_clear();
    }

    public function handle_filter_redirect_filter($status)
    {
        $this->get_logger()->debug('Redirect happend, flagging request as non-cacheable');

        \add_filter('vendi/cache/do_not_cache_request', '__return_true');

        return $status;
    }

    public function handle_action_publish_post($post_id)
    {
        $this->get_logger()->debug('Post/page published, scheduling cache clear', [ 'post_id' => $post_id ]);
        $permalink = \get_permalink($post_id);
        $this->delete_file_from_permalink($permalink);
        $this->schedule_cache_clear();
    }

    public function delete_file_from_permalink($permalink)
    {
        $this->get_logger()->debug('Request to delete cache file by permalink', [ 'permalink' => $permalink ]);

        $cache_file = $this->get_cache_key_generator()->local_cache_filename_from_url($permalink);

        $this->get_logger()->debug('Permalink resolved to cache file', [ 'cache_file' => $cache_file ]);

        if ($this->get_maestro()->get_file_system()->file_exists($cache_file)) {
            $this->get_logger()->debug('Cache file found, deleting', [ 'cache_file' => $cache_file ]);
            $this->get_maestro()->get_file_system()->delete_file($cache_file);
        } else {
            $this->get_logger()->debug('Cache file not found, skipping', [ 'cache_file' => $cache_file ]);
        }
    }

    public function clear_entire_page_cache()
    {
        $this->get_logger()->info('Starting clearing of page cache');

        $cache_folder = $this
                            ->get_maestro()
                            ->get_secretary()
                            ->get_cache_folder_abs()
                        ;

        $result = $this
                    ->get_maestro()
                    ->get_file_system()
                    ->delete_dir_abs($cache_folder)
                ;

        if (! $result) {
            $this->get_logger()->error('Unable to clear page cache');
            return;
        }

        $this->get_logger()->info('Page cache successfully cleared');
    }

    public function schedule_cache_clear()
    {
        if ($this->_was_cache_clear_schedule_yet) {
            $this->get_logger()->debug('Request to schedule cache clear ignored because the schedule already exists for this request');
            return;
        }

        $this->_was_cache_clear_schedule_yet = true;

        $this->get_logger()->debug('Request to schedule cache clear received');

        //rand makes sure this is called every time and isn't subject to the
        //10 minute window where the same event won't be run twice with
        //wp_schedule_single_event
        \wp_schedule_single_event(\time() - 15, self::ACTION_NAME__CACHE_CLEAR, [ \rand(0, 999999999) ]);
        $url = \admin_url('admin-ajax.php');

        $this->get_logger()->debug('Invoking URL to kick-off cron to clear cache');
        \wp_remote_get($url);
    }

    //Can safely call this as many times as we like because it'll only schedule one clear
    public function handle_action_clear_page_cache()
    {
        $this->schedule_cache_clear();
    }
}
