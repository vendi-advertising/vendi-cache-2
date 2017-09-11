<?php

namespace Vendi\Cache;

use Vendi\Shared\utils;

class CacheSettings
{
    private static $_instance;

    private function __construct()
    {

    }

    public static function get_instance()
    {
        if( ! self::$_instance )
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get_cache_folder_abs()
    {
        //If this is defined, use it directly
        if( defined( 'VENDI_CACHE_FOLDER_ABS' ) )
        {
            return VENDI_CACHE_FOLDER_ABS;
        }

        //If this is defined, use it relative to wp-content
        if( defined( 'VENDI_CACHE_FOLDER_NAME' ) )
        {
            return \Webmozart\PathUtil\Path::join( WP_CONTENT_DIR, VENDI_CACHE_FOLDER_NAME );
        }

        //Default, return ABS path to wp-content/vendi_cache
        return \Webmozart\PathUtil\Path::join( WP_CONTENT_DIR, 'vendi_cache' );
    }

    /**
     * The absolute path to the log file.
     *
     * @return string
     */
    public function get_log_file_abs()
    {
        if( defined( 'VENDI_CACHE_LOG_FILE_ABS' ) )
        {
            return VENDI_CACHE_LOG_FILE_ABS;
        }

        return \Webmozart\PathUtil\Path::join( $this->get_cache_folder_abs(), 'vendi_cache.log' );
    }

    /**
     * The maximum age in seconds that a file should exist in the cache.
     * @return int
     */
    public function get_max_file_age()
    {
        if( defined( 'VENDI_CACHE_MAX_FILE_AGE' ) )
        {
            return (int)VENDI_CACHE_MAX_FILE_AGE;
        }

        return 10000;
    }

    /**
     * The minimum byte size for a request to cache.
     * @return int
     */
    public function get_min_page_size()
    {
        if( defined( 'VENDI_CACHE_MIN_PAGE_SIZE' ) )
        {
            return (int)VENDI_CACHE_MIN_PAGE_SIZE;
        }

        return 1000;
    }

    public function get_fs_permissions_for_cache()
    {
        return [
                'file' =>
                            [
                                'public'  => defined( 'VENDI_CACHE_FS_PERM_FILE_PUBLIC')  ? VENDI_CACHE_FS_PERM_FILE_PUBLIC  : 0664,
                                'private' => defined( 'VENDI_CACHE_FS_PERM_FILE_PRIVATE') ? VENDI_CACHE_FS_PERM_FILE_PRIVATE : 0664,
                            ],
                'dir' =>
                            [
                                'public'  => defined( 'VENDI_CACHE_FS_PERM_DIR_PUBLIC')   ? VENDI_CACHE_FS_PERM_DIR_PUBLIC   : 0755,
                                'private' => defined( 'VENDI_CACHE_FS_PERM_DIR_PRIVATE')  ? VENDI_CACHE_FS_PERM_DIR_PRIVATE  : 0755,
                            ]
            ];
    }

    public function get_fs_permission_for_log()
    {
        return defined( 'VENDI_CACHE_FS_PERM_LOG') ? VENDI_CACHE_FS_PERM_LOG : 0664;
    }

    public function get_logging_level()
    {
        if( defined( 'VENDI_CACHE_LOGGING_LEVEL' ) )
        {
            return (int)VENDI_CACHE_LOGGING_LEVEL;
        }

        return \Monolog\Logger::DEBUG;
    }
}
