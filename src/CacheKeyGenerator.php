<?php

namespace Vendi\Cache;

use Symfony\Component\HttpFoundation\Request;

class CacheKeyGenerator
{

    private static $_urls_to_files = [];

    private static $_urls_to_files_cache_lookups = [];

    public static function get_mapping_of_urls_to_files() : array
    {
        return self::$_urls_to_files;
    }

    public static function get_cache_lookup_counts_for_url( $url )
    {
        if( ! array_key_exists( $url, self::$_urls_to_files_cache_lookups ) )
        {
            return -1;
        }

        return self::$_urls_to_files_cache_lookups[ $url ];
    }

    public static function sanitize_host_for_cache_filename( $host )
    {
        return preg_replace( '/[^a-zA-Z0-9\-\.]+/', '', $host );
    }

    /**
     * [create_url_from_server_variables description]
     *
     * @return [type] [description]
     */
    public static function create_url_from_server_variables()
    {
        $req = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        return $req->getUri();
    }

    public static function local_cache_filename_from_url( $url = null )
    {
        if( ! $url )
        {
            $url = self::create_url_from_server_variables();
        }

        //See if we've previously determined the file for this URL
        if( array_key_exists( $url, self::$_urls_to_files ) )
        {
            //Increment a shared global counter, used for testing
            self::$_urls_to_files_cache_lookups[ $url ] += 1;
            return self::$_urls_to_files[ $url ];
        }

        $parts = parse_url( $url );

        $host = self::sanitize_host_for_cache_filename( $parts[ 'host' ] );
        $path = self::sanitize_path_for_cache_filename( $parts[ 'path' ] );
        $ext = '';
        if( 'https' === $parts[ 'scheme' ] )
        {
            $ext = '_https';
        }

        $file = sprintf(
                            '%1$s_%2$s_%3$s%4$s.html',
                            $host,
                            $path,
                            'vendi_cache',
                            $ext
                        );

        //Cache this url and file for future use
        self::$_urls_to_files[ $url ] = $file;

        //Create an entry in the global counters for this URL
        self::$_urls_to_files_cache_lookups[ $url ] = 0;

        return $file;
    }

    public static function sanitize_path_for_cache_filename( $path )
    {
        //Strip out bad chars and multiple dots
        $path = preg_replace( '/(?:[^a-zA-Z0-9\-\_\.\~\/]+|\.{2,})/', '', $path );

        if( preg_match( '/\/*([^\/]*)\/*([^\/]*)\/*([^\/]*)\/*([^\/]*)\/*([^\/]*)(.*)$/', $path, $matches ) )
        {
            $path = $matches[ 1 ] . '/';
            for( $i = 2; $i <= 6; $i++ )
            {
                $path .= strlen( $matches[ $i ] ) > 0 ? $matches[ $i ] : '';
                $path .= $i < 6 ? '~' : '';
            }
        }
        return $path;
    }
}
