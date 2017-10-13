<?php

namespace Vendi\Cache;

use League\Flysystem\Filesystem;
use Monolog\Logger;
use Vendi\Cache\Auditing;
use Vendi\Cache\CacheExclusions;
use Vendi\Cache\CacheKeyGenerator;
use Vendi\Cache\Secretary;
use Vendi\Cache\ErrorHandler;

final class CacheMaster
{
    //Set to true after all hooks are setup
    private $_caching_hooks_setup = false;

    private $_was_cache_clear_schedule_yet = false;

    const ACTION_NAME__CACHE_CLEAR = 'vendi/cache/clear';

    const LEGACY_FILTER_NAME__NO_CACHE = 'vendi-cache/do-not-cache';

    private $_is_request_cacheable = null;

    private $_maestro = null;

    private $_cache_key_generator = null;

    private $_updater = null;

    public function __construct( Maestro $maestro )
    {
        $this->_maestro = $maestro;
    }

    /**
     * [get_updater description]
     * @return UpdaterInterface
     */
    public function get_updater()
    {
        if( ! $this->_updater instanceof PluginUpdater )
        {
            $this->_updater = new PluginUpdater( $this->get_maestro() );
        }

        return $this->_updater;
    }

    public function get_cache_key_generator()
    {
        if( ! $this->_cache_key_generator instanceof CacheKeyGenerator )
        {
            $this->_cache_key_generator = new CacheKeyGenerator( $this->get_maestro() );
        }

        return $this->_cache_key_generator;
    }

    /**
     * [get_maestro description]
     * @return Maestro
     */
    public function get_maestro()
    {
        return $this->_maestro;
    }

    /**
     * [get_logger description]
     * @return Logger
     */
    public function get_logger()
    {
        return $this->get_maestro()->get_logger();
    }

    /**
     * [get_secretary description]
     * @return Secretary
     */
    public function get_secretary()
    {
        return $this->get_maestro()->get_secretary();
    }

    /**
     * [get_file_system description]
     * @return Filesystem
     */
    public function get_file_system()
    {
        return $this->get_maestro()->get_file_system();
    }

    public function log_request_as_not_cacheable( array $args )
    {
        $this->get_logger()->info( __( 'Request not cacheable', 'vendi-cache' ), $args );
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
        $this->get_logger()->debug( 'AJAX request found, only listening for cache clear' );
        add_action( self::ACTION_NAME__CACHE_CLEAR, array( $this, 'clear_entire_page_cache' ) );
        return;
    }

    public function _setup_main_hooks()
    {
        $this->get_logger()->debug( 'Setting up caching hooks' );

        if( $this->_caching_hooks_setup )
        {
            $this->get_logger()->warning( 'Caching hooks already setup' );
            return;
        }

        add_action( self::ACTION_NAME__CACHE_CLEAR, array( $this, 'clear_entire_page_cache' ) );

        //In theory this should be put at the bottom
        $this->_caching_hooks_setup = true;

        if( $this->is_user_logged_in() )
        {
            $this->get_logger()->debug( 'User is logged in, additional hooks added' );
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

            $request = $this->get_maestro()->get_request();

            if( $request->isMethod( 'POST' ) )
            {
                $pages = [
                            '/wp-admin/options.php',
                            '/wp-admin/options-permalink.php',
                        ];

                $current_page = strtolower( $request->getBaseUrl() . $request->getPathInfo() );

                foreach( $pages as $page )
                {
                    if( $page == $current_page )
                    {
                        $this->get_logger()->debug( 'POST to special admin page, clearing cache', [ 'page' => $page ] );
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
        $request = $this->get_maestro()->get_request();

        if( $request->isMethod( 'GET' ) )
        {
            return;
        }

        $cache_file = $this->get_cache_key_generator()->local_cache_filename_from_url();

        if( $this->file_exists( $cache_file ) )
        {
            $this->get_logger()->info(
                                        'Non-GET request received, evicting cache file',
                                        [
                                            'cache_file' => $cache_file,
                                            'method' => $request->getMethod()
                                        ]
                                    );
        }
    }

    public function is_request_cacheable()
    {
        $test_runner = new CacheBypassTester( $this->get_maestro() );
        if( false === $test_runner->test_request() )
        {
            return false;
        }
    }

    public function _flag_request_as_cacheable()
    {
        if( false === $this->_is_request_cacheable )
        {
            $this->get_logger()->error( 'Request previously marked as not cacheable, cannot undo action' );
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
        if( false === $this->is_request_cacheable() )
        {
            $this->_flag_request_as_not_cacheable();
            return;
        }

        $this->get_logger()->debug( 'Request marked as cacheable' );
        $this->_flag_request_as_cacheable();

        $cache_file = $this->get_cache_key_generator()->local_cache_filename_from_url( );


        if( ! $this->file_exists( $cache_file ) )
        {
            $this->get_logger()->debug( 'Cache file not found', [ 'cache_file' => $cache_file ] );
            return;
        }

        $this->get_logger()->debug( 'Cache file found', [ 'cache_file' => $cache_file ] );

        $stat = @stat( $cache_file );
        if( ! $stat )
        {
            $this->get_logger()->warning( 'Could not get file stats', [ 'cache_file' => $cache_file ] );
            return;
        }

        $age = time() - $stat[ 'mtime' ];
        if( $age >= $this->get_secretary()->get_max_file_age() )
        {
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
        readfile( $cache_file );

        //Terminate the request
        exit ;
    }

    public function _setup_actual_request_caching()
    {
        if( true !== $this->_is_request_cacheable )
        {
            $this->get_logger()->debug( 'Not setting up page caching, previous check flagged request as non-cacheable' );
            return;
        }

        $this->get_logger()->debug( 'Setting up page caching' );

        //Do not cache fatal errors
        global $vendi_cache_old_error_handler;
        $vendi_cache_old_error_handler = set_error_handler( array( 'Vendi\Cache\ErrorHandler', 'handle_error' ) );

        global $vendi_cache_old_exception_handler;
        $vendi_cache_old_exception_handler = set_exception_handler( array( 'Vendi\Cache\ErrorHandler', 'handle_exception' ) );

        ob_start( array( __CLASS__, 'handle_ob_complete' ) ); //Setup routine to store the file
    }

    public function handle_ob_complete( $buffer = '' )
    {
        if( function_exists( 'is_404' ) && is_404() )
        {
            $this->log_request_as_not_cacheable( [ 'reason' => '404 detected' ] );
            return false;
        }

        if( defined( 'VENDI_CACHE_PHP_ERROR' ) )
        {
            $this->log_request_as_not_cacheable( [ 'reason' => 'Explicit constant detected', 'constant' => 'VENDI_CACHE_PHP_ERROR' ] );
            return $buffer;
        }

        if( apply_filters( self::LEGACY_FILTER_NAME__NO_CACHE, false, $buffer ) )
        {
            $this->log_request_as_not_cacheable( [ 'reason' => 'Legacy filter return no cache', 'filter' => self::LEGACY_FILTER_NAME__NO_CACHE ] );
            return $buffer;
        }

        //The average web page size is 1246,000 bytes. If web page is less than 1000 bytes, don't cache it.
        //TODO: Move to option
        if( strlen( $buffer ) < $this->get_secretary()->get_min_page_size() )
        {
            $this->log_request_as_not_cacheable( [ 'reason' => 'Page too small', 'size' => strlen( $buffer ), 'min_size' => $this->get_secretary()->get_min_page_size() ] );
            return $buffer;
        }

        $request = $this->get_maestro()->get_request();

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
            $append .= 'Time created on server: ' . date( 'Y-m-d H:i:s T' ) . '. ';
            $append .= 'Protocol: ' . ( $this->is_https_page() ? 'HTTPS' : 'HTTP' ) . '. ';
            $append .= 'Page size: ' . strlen( $buffer ) . ' bytes. ';

            $host = wp_kses( $request->getHttpHost(), array() );

            $append .= 'Host: ' . $host . '. ';
            $append .= 'Request URI: ' . wp_kses( $request->getBaseUrl() . $request->getPathInfo(), array() ) . ' ';
            $appendGzip = $append . " Encoding: GZEncode -->\n";
            $append .= " Encoding: Uncompressed -->\n";
        // }

        $this->get_logger()->info( 'Caching file', [ 'cache_file' => $cache_file ] );
        $this->write_file( $cache_file, $buffer . $append );
        // chmod( $file, 0644 );
        // if( self::$cacheType == cache_settings::CACHE_MODE_ENHANCED )
        // {
            //create gzipped files so we can send precompressed files
            $cache_file .= '_gzip';
            $this->get_logger()->info( 'Caching file gzip', [ 'cache_file' => $cache_file ] );
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

        return $this->get_maestro()->get_request()->isSecure();
    }

    public function setup_caching()
    {
        if( defined( 'DOING_AJAX' ) && DOING_AJAX )
        {
            $this->_set_ajax_only_hooks();
            return;
        }

        $this->_setup_main_hooks();

        $request = $this->get_maestro()->get_request();

        $this->get_logger()->debug(
                                                        'Request URL is:',
                                                        [
                                                            'host'   => $request->getHttpHost(),
                                                            'path'   => $request->getBaseUrl() . $request->getPathInfo(),
                                                            'secure' => $this->is_https_page(),
                                                        ]
                                                    );

        $this->_maybe_purge_cached_file_on_non_GET_method();

        $this->_maybe_serve_cached_file();

        $this->_setup_actual_request_caching();
    }

    public function handle_action_comment_post( $comment_id )
    {
        $this->get_logger()->debug( 'Comment posted, scheduling cache clear', [ 'comment_id' => $comment_id ] );

        $c = get_comment( $comment_id, ARRAY_A );
        $perm = get_permalink( $c[ 'comment_post_ID' ] );
        $this->delete_file_from_permalink( $perm );
        $this->schedule_cache_clear();
    }

    public function handle_filter_redirect_filter( $status )
    {
        $this->get_logger()->debug( 'Redirect happend, flagging request as non-cacheable' );

        add_filter( 'vendi/cache/do_not_cache_request', '__return_true' );

        return $status;
    }

    public function handle_action_publish_post( $post_id )
    {
        $this->get_logger()->debug( 'Post/page published, scheduling cache clear', [ 'post_id' => $post_id ] );
        $permalink = get_permalink( $post_id );
        $this->delete_file_from_permalink( $permalink );
        $this->schedule_cache_clear();
    }

    public function delete_file_from_permalink( $permalink )
    {
        $this->get_logger()->debug( 'Request to delete cache file by permalink', [ 'permalink' => $permalink ] );

        $cache_file = $this->get_cache_key_generator()->local_cache_filename_from_url( $permalink );

        $this->get_logger()->debug( 'Permalink resolved to cache file', [ 'cache_file' => $cache_file ] );

        if( $this->file_exists( $cache_file ) )
        {
            $this->get_logger()->debug( 'Cache file found, deleting', [ 'cache_file' => $cache_file ] );
            $this->delete_file( $cache_file );
        }
        else
        {
            $this->get_logger()->debug( 'Cache file not found, skipping', [ 'cache_file' => $cache_file ] );
        }
    }

    public function clear_entire_page_cache( )
    {
        $this->get_logger()->info( 'Starting clearing of page cache' );

        if( ! $this->delete_cache_dir_contents() )
        {
            $this->get_logger()->error( 'Unable to clear page cache' );
            return;
        }

        $this->get_logger()->info( 'Page cache successfully cleared' );
    }

    public function schedule_cache_clear()
    {
        if( $this->_was_cache_clear_schedule_yet )
        {
            $this->get_logger()->debug( 'Request to schedule cache clear ignored because the schedule already exists for this request' );
            return;
        }

        $this->_was_cache_clear_schedule_yet = true;

        $this->get_logger()->debug( 'Request to schedule cache clear received' );

        //rand makes sure this is called every time and isn't subject to the
        //10 minute window where the same event won't be run twice with
        //wp_schedule_single_event
        wp_schedule_single_event( time() - 15, self::ACTION_NAME__CACHE_CLEAR, array( rand( 0, 999999999 ) ) );
        $url = admin_url( 'admin-ajax.php' );

        $this->get_logger()->debug( 'Invoking URL to kick-off cron to clear cache' );
        wp_remote_get( $url );
    }

    //Can safely call this as many times as we like because it'll only schedule one clear
    public function handle_action_clear_page_cache()
    {
        $this->schedule_cache_clear();
    }

    public function file_exists( $relative_file_path )
    {
        return $this->get_file_system()->has( $relative_file_path );
    }

    public function delete_file( $relative_file_path )
    {
        return $this->get_file_system()->delete( $relative_file_path );
    }

    public function write_file( $relative_file_path, $contents )
    {
        return $this->get_file_system()->write( $relative_file_path, $contents );
    }

    public function delete_cache_dir_contents( $absolute_path = null )
    {
        //Allow callers to optionally supply the path
        if( ! $absolute_path )
        {
            $absolute_path = $this->get_secretary()->get_cache_folder_abs();
        }

        //Log the start of deletion
        $this->get_logger()->debug( 'Delete directory request', [ 'dir' => $absolute_path ] );

        //If we don't have an actual folder, skip it
        if( ! is_dir( $absolute_path ) )
        {
            $this->get_logger()->debug( 'Directory empty... skipping', [ 'dir' => $absolute_path ] );
            return false;
        }

        //We only want folders, not files, so we'll use the ListPaths plugin
        //to get only those
        $this->get_file_system()->addPlugin( new ListPaths() );
        $child_paths = $this->get_file_system()->listPaths( );

        //We don't want to delete
        $log_file_abs = \Webmozart\PathUtil\Path::canonicalize( $this->get_secretary()->get_log_file_abs() );

        foreach( $child_paths as $dir )
        {

            $test_file_path = \Webmozart\PathUtil\Path::join(
                                                                $this->get_file_system()->getAdapter()->applyPathPrefix( $dir ),
                                                                $this->get_secretary()->get_log_file_name()
                                                            );

            if( $test_file_path === $log_file_abs )
            {
                $this->get_logger()->debug( 'Skipping log directory', [ 'path' => $dir, 'is_dir' => is_dir( $absolute_path ) ] );
                continue;
            }

            $result = $this->get_file_system()->deleteDir( $dir );
            if( ! $result )
            {
                $this->get_logger()->error( 'Could not delete directory', [ 'dir' => $dir ] );
                return false;
            }

            $this->get_logger()->debug( 'Delete directory', [ 'dir' => $dir ] );
        }

        return true;

    }

}
