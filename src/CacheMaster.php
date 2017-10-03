<?php

namespace Vendi\Cache;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\ListPaths;
use League\Flysystem\{AdapterInterface, Filesystem};
use Vendi\Cache\{Auditing, CacheExclusions, CacheKeyGenerator, CacheSettings, ErrorHandler};
use Vendi\Shared\utils;

final class CacheMaster
{
    private static $_instance;

    private $_file_system;

    //Set to true after all hooks are setup
    private $_caching_hooks_setup = false;

    private $_was_cache_clear_schedule_yet = false;

    const ACTION_NAME__CACHE_CLEAR = 'vendi/cache/clear';

    const LEGACY_FILTER_NAME__NO_CACHE = 'vendi-cache/do-not-cache';

    private $_is_request_cacheable = null;

    private $_local_cache_file_name = null;

    private function __construct()
    {
        $adapter = new Local(
                                //The folder to cache to
                                CacheSettings::get_instance()->get_cache_folder_abs(),

                                //Use locks during write (default)
                                LOCK_EX,

                                //Throw exception on symlinks (default)
                                Local::DISALLOW_LINKS,

                                //Special file system permissions
                                CacheSettings::get_instance()->get_fs_permissions_for_cache()
                            );

        $this->_file_system = new Filesystem(
                                                $adapter,
                                                [
                                                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                                                ]
                                            );
    }

    public static function get_instance()
    {
        if( ! self::$_instance )
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Checks if the current visitor is a logged in user.
     *
     * This function does not exist at cache setup time so it has been
     * duplicated here.
     *
     * @return boolean True if user is logged in, false if not logged in.
     */
    public function is_user_logged_in()
    {
        $user = \wp_get_current_user();
        return $user->exists();
    }

    public function _set_ajax_only_hooks()
    {
        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'AJAX request found, only listening for cache clear' );
        add_action( self::ACTION_NAME__CACHE_CLEAR, array( $this, 'clear_entire_page_cache' ) );
        return;
    }

    public function _setup_main_hooks()
    {
        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Setting up caching hooks' );

        if( $this->_caching_hooks_setup )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->warning( 'Caching hooks already setup' );
            return;
        }

        add_action( self::ACTION_NAME__CACHE_CLEAR, array( $this, 'clear_entire_page_cache' ) );

        //In theory this should be put at the bottom
        $this->_caching_hooks_setup = true;

        if( $this->is_user_logged_in() )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'User is logged in, additional hooks added' );
            add_action( 'publish_post', array( $this, 'handle_action_publish_post' ) );
            add_action( 'publish_page', array( $this, 'handle_action_publish_post' ) );

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

            foreach( $hooks as $action )
            {
                //Schedules a cache clear for immediately so it won't lag current request.
                add_action( $action, array( $this, 'handle_action_clear_page_cache' ) );
            }

            if( utils::is_post() )
            {
                $pages = [
                            '/wp-admin/options.php',
                            '/wp-admin/options-permalink.php',
                        ];

                $current_page = strtolower( utils::get_server_value( 'REQUEST_URI' ) );

                foreach( $pages as $page )
                {
                    if( $page == $current_page )
                    {
                        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'POST to special admin page, clearing cache', [ 'page' => $page ] );
                        $this->schedule_cache_clear();
                        break;
                    }
                }
            }
        }

        //Might not be logged in
        add_action( 'comment_post', array( $this, 'handle_action_comment_post' ) );
        add_filter( 'wp_redirect', array( $this, 'handle_filter_redirect_filter' ) );
    }

    public function _maybe_purge_cached_file_on_non_GET_method()
    {
        if( utils::is_request_method( 'GET' ) )
        {
            return;
        }

        $cache_file = CacheKeyGenerator::local_cache_filename_from_url();

        if( $this->file_exists( $cache_file ) )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->info( 'Non-GET request received, evicting cache file', [ 'cache_file' => $cache_file, 'method' => utils::get_server_value( 'REQUEST_METHOD' ) ] );
        }
    }

    public function is_request_cacheable()
    {
        if( ! CacheBypassTester::get_instance()->test_request() )
        {
            return false;
        }
    }

    public function _flag_request_as_cacheable()
    {
        if( false === $this->_is_request_cacheable )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->error( 'Request previously marked as not cacheable, cannot undo action' );
            return;
        }

        $this->_is_request_cacheable = true;
    }

    public function _flag_request_as_not_cacheable()
    {
        $this->_is_request_cacheable = false;
    }

    public function _maybe_serve_cached_file()
    {
        if( ! $this->is_request_cacheable() )
        {
            $this->_flag_request_as_not_cacheable();
            return;
        }

        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Request marked as cacheable' );
        $this->_flag_request_as_cacheable();

        $cache_file = CacheKeyGenerator::local_cache_filename_from_url( );


        if( ! $this->file_exists( $cache_file ) )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Cache file not found', [ 'cache_file' => $cache_file ] );
            return;
        }

        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Cache file found', [ 'cache_file' => $cache_file ] );

        $stat = @stat( $cache_file );
        if( ! $stat )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->warning( 'Could not get file stats', [ 'cache_file' => $cache_file ] );
            return;
        }

        $age = time() - $stat[ 'mtime' ];
        if( $age >= CacheSettings::get_instance()->get_max_file_age() )
        {
            //TODO: Should we delete the file?

            \Vendi\Cache\Logging::get_instance()->get_logger()->debug(
                                                            'Stale cache file found, skipping',
                                                            [
                                                                'cache_file' => $cache_file,
                                                                'age' => $age,
                                                                'max_age' => CacheSettings::get_instance()->get_max_file_age(),
                                                            ]
                                                        );
            return;
        }

        \Vendi\Cache\Logging::get_instance()->get_logger()->debug(
                                                        'Serving cache file',
                                                        [
                                                            'cache_file' => $cache_file,
                                                            'age' => $age,
                                                            'max_age' => CacheSettings::get_instance()->get_max_file_age(),
                                                        ]
                                                    );

        //sends file to stdout
        readfile( $cache_file );

        //Terminate the request
        exit ;
    }

    public function _setup_actual_request_caching()
    {
        if( true !== $this->_is_request_cacheable )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Not setting up page caching, previous check flagged request as non-cacheable' );
            return;
        }

        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Setting up page caching' );

        //Do not cache fatal errors
        global $vendi_cache_old_error_handler;
        $vendi_cache_old_error_handler = set_error_handler( array( ErrorHandler::class, 'handle_error' ) );

        global $vendi_cache_old_exception_handler;
        $vendi_cache_old_exception_handler = set_exception_handler( array( ErrorHandler::class, 'handle_exception' ) );

        ob_start( array( __CLASS__, 'handle_ob_complete' ) ); //Setup routine to store the file
    }

    public function handle_ob_complete( $buffer = '' )
    {
        if( function_exists( 'is_404' ) && is_404() )
        {
            \Vendi\Cache\Logging::log_request_as_not_cacheable( [ 'reason' => '404 detected' ] );
            return false;
        }

        if( defined( 'VENDI_CACHE_PHP_ERROR' ) )
        {
            \Vendi\Cache\Logging::log_request_as_not_cacheable( [ 'reason' => 'Explicit constant detected', 'constant' => 'VENDI_CACHE_PHP_ERROR' ] );
            return $buffer;
        }

        if( apply_filters( self::LEGACY_FILTER_NAME__NO_CACHE, false, $buffer ) )
        {
            \Vendi\Cache\Logging::log_request_as_not_cacheable( [ 'reason' => 'Legacy filter return no cache', 'filter' => self::LEGACY_FILTER_NAME__NO_CACHE ] );
            return $buffer;
        }

        //The average web page size is 1246,000 bytes. If web page is less than 1000 bytes, don't cache it.
        //TODO: Move to option
        if( strlen( $buffer ) < CacheSettings::get_instance()->get_min_page_size() )
        {
            \Vendi\Cache\Logging::log_request_as_not_cacheable( [ 'reason' => 'Page too small', 'size' => strlen( $buffer ), 'min_size' => CacheSettings::get_instance()->get_min_page_size() ] );
            return $buffer;
        }

        // \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Buffer', [ 'buffer' => strlen( $buffer ) ] );


        $cache_file = CacheKeyGenerator::local_cache_filename_from_url();

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
            $append .= 'Time created on server: ' . date( 'Y-m-d H:i:s T' ) . '. ';
            $append .= 'Protocol: ' . ( $this->is_https_page() ? 'HTTPS' : 'HTTP' ) . '. ';
            $append .= 'Page size: ' . strlen( $buffer ) . ' bytes. ';

            $host = wp_kses(
                                utils::get_server_value( 'HTTP_HOST', utils::get_server_value( 'SERVER_NAME' ) ),
                                array()
                        );
            $append .= 'Host: ' . $host . '. ';
            $append .= 'Request URI: ' . wp_kses( utils::get_server_value( 'REQUEST_URI' ), array() ) . ' ';
            $appendGzip = $append . " Encoding: GZEncode -->\n";
            $append .= " Encoding: Uncompressed -->\n";
        // }

        \Vendi\Cache\Logging::get_instance()->get_logger()->info( 'Caching file', [ 'cache_file' => $cache_file ] );
        $this->write_file( $cache_file, $buffer . $append );
        // chmod( $file, 0644 );
        // if( self::$cacheType == cache_settings::CACHE_MODE_ENHANCED )
        // {
            //create gzipped files so we can send precompressed files
            $cache_file .= '_gzip';
            \Vendi\Cache\Logging::get_instance()->get_logger()->info( 'Caching file gzip', [ 'cache_file' => $cache_file ] );
            $this->write_file( $cache_file, gzencode( $buffer . $appendGzip, 9 ) );
            // @file_put_contents( $file, gzencode( $buffer . $appendGzip, 9 ), LOCK_EX );
            // chmod( $file, 0644 );
        // }
        return $buffer;

    }

    /**
     * @return boolean True if the reqeusted page was an HTTPS page, otherwise false.
     */
    public function is_https_page()
    {
        //Prefer a core check since this is in flux right now
        if( is_ssl() )
        {
            return true;
        }

        //In case we're behind a proxy and user used HTTPS.
        if( 'https' === utils::get_server_value( 'HTTP_X_FORWARDED_PROTO' ) )
        {
            return true;
        }

        return false;
    }

    public function setup_caching()
    {
        if( defined( 'DOING_AJAX' ) && DOING_AJAX )
        {
            $this->_set_ajax_only_hooks();
            return;
        }

        $this->_setup_main_hooks();

        \Vendi\Cache\Logging::get_instance()->get_logger()->debug(
                                                        'Request URL is:',
                                                        [
                                                            'host'   => utils::get_server_value( 'HTTP_HOST' ),
                                                            'path'   => utils::get_server_value( 'REQUEST_URI' ),
                                                            'secure' => $this->is_https_page(),
                                                        ]
                                                    );

        $this->_maybe_purge_cached_file_on_non_GET_method();

        $this->_maybe_serve_cached_file();

        $this->_setup_actual_request_caching();
    }

    public static function handle_action_comment_post( $comment_id )
    {
        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Comment posted, scheduling cache clear', [ 'comment_id' => $comment_id ] );

        $c = get_comment( $comment_id, ARRAY_A );
        $perm = get_permalink( $c[ 'comment_post_ID' ] );
        $this->delete_file_from_permalink( $perm );
        $this->schedule_cache_clear();
    }

    public static function handle_filter_redirect_filter( $status )
    {
        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Redirect happend, flagging request as non-cacheable' );

        add_filter( 'vendi/cache/do_not_cache_request', '__return_true' );

        return $status;
    }

    public function handle_action_publish_post( $post_id )
    {
        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Post/page published, scheduling cache clear', [ 'post_id' => $post_id ] );
        $permalink = get_permalink( $post_id );
        $this->delete_file_from_permalink( $permalink );
        $this->schedule_cache_clear();
    }

    public function delete_file_from_permalink( $permalink )
    {
        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Request to delete cache file by permalink', [ 'permalink' => $permalink ] );

        $cache_file = CacheKeyGenerator::local_cache_filename_from_url( $permalink );

        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Permalink resolved to cache file', [ 'cache_file' => $cache_file ] );

        if( CacheMaster::get_instance()->file_exists( $cache_file ) )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Cache file found, deleting', [ 'cache_file' => $cache_file ] );
            CacheMaster::get_instance()->delete_file( $cache_file );
        }
        else
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Cache file not found, skipping', [ 'cache_file' => $cache_file ] );
        }
    }

    public function clear_entire_page_cache( )
    {
        \Vendi\Cache\Logging::get_instance()->get_logger()->info( 'Starting clearing of page cache' );

        if( ! $this->delete_cache_dir_contents() )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->error( 'Unable to clear page cache' );
            return;
        }

        \Vendi\Cache\Logging::get_instance()->get_logger()->info( 'Page cache successfully cleared' );
    }

    public function schedule_cache_clear()
    {
        if( $this->_was_cache_clear_schedule_yet )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Request to schedule cache clear ignored because the schedule already exists for this request' );
            return;
        }

        $this->_was_cache_clear_schedule_yet = true;

        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Request to schedule cache clear received' );

        //rand makes sure this is called every time and isn't subject to the
        //10 minute window where the same event won't be run twice with
        //wp_schedule_single_event
        wp_schedule_single_event( time() - 15, self::ACTION_NAME__CACHE_CLEAR, array( rand( 0, 999999999 ) ) );
        $url = admin_url( 'admin-ajax.php' );

        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Invoking URL to kick-off cron to clear cache' );
        wp_remote_get( $url );
    }

    //Can safely call this as many times as we like because it'll only schedule one clear
    public static function handle_action_clear_page_cache()
    {
        $this->schedule_cache_clear();
    }


    public function file_exists( $relative_file_path )
    {
        return $this->_file_system->has( $relative_file_path );
    }

    public function delete_file( $relative_file_path )
    {
        return $this->_file_system->delete( $relative_file_path );
    }

    public function write_file( $relative_file_path, $contents )
    {
        return $this->_file_system->write( $relative_file_path, $contents );
    }

    public function delete_cache_dir_contents( $absolute_path = null )
    {
        //Allow callers to optionally supply the path
        if( ! $absolute_path )
        {
            $absolute_path = CacheSettings::get_instance()->get_cache_folder_abs();
        }

        //Log the start of deletion
        \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Delete directory request', [ 'dir' => $absolute_path ] );

        //If we don't have an actual folder, skip it
        if( ! is_dir( $absolute_path ) )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Directory empty... skipping', [ 'dir' => $absolute_path ] );
            return false;
        }

        //We only want folders, not files, so we'll use the ListPaths plugin
        //to get only those
        $this->_file_system->addPlugin( new ListPaths() );
        $child_paths = $this->_file_system->listPaths( );

        //We don't want to delete
        $log_file_abs = \Webmozart\PathUtil\Path::canonicalize( CacheSettings::get_instance()->get_log_file_abs() );

        foreach( $child_paths as $dir )
        {

            $test_file_path = \Webmozart\PathUtil\Path::join(
                                                                $this->_file_system->getAdapter()->applyPathPrefix( $dir ),
                                                                CacheSettings::get_instance()->get_log_file_name()
                                                            );

            if( $test_file_path === $log_file_abs )
            {
                \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Skipping log directory', [ 'path' => $dir, 'is_dir' => is_dir( $absolute_path ) ] );
                continue;
            }

            $result = $this->_file_system->deleteDir( $dir );
            if( ! $result )
            {
                \Vendi\Cache\Logging::get_instance()->get_logger()->error( 'Could not delete directory', [ 'dir' => $dir ] );
                return false;
            }

            \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Delete directory', [ 'dir' => $dir ] );
        }

        return true;

    }
}
