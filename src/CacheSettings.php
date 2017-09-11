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

    public function get_log_file_abs()
    {
        if( defined( 'VENDI_CACHE_LOG_FILE_ABS' ) )
        {
            return VENDI_CACHE_LOG_FILE_ABS;
        }

        return \Webmozart\PathUtil\Path::join( $this->get_cache_folder_abs(), 'vendi_cache.log' );
    }

    public function get_max_file_age()
    {
        if( defined( 'VENDI_CACHE_MAX_FILE_AGE' ) )
        {
            return (int)VENDI_CACHE_MAX_FILE_AGE;
        }

        return 10000;
    }

    public function get_min_page_size()
    {
        if( defined( 'VENDI_CACHE_MIN_PAGE_SIZE' ) )
        {
            return (int)VENDI_CACHE_MIN_PAGE_SIZE;
        }

        return 1000;
    }
}
