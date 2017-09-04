<?php

namespace Vendi\Cache;

use Vendi\Shared\utils;

class CacheKeyGenerator
{

    public static function sanitize_host_for_cache_filename( $host )
    {
        return preg_replace( '/[^a-zA-Z0-9\-\.]+/', '', $host );
    }

    /**
     * [create_url_from_server_variables description]
     *
     * @see https://stackoverflow.com/a/8389271/231316
     *
     * @return [type] [description]
     */
    public static function create_url_from_server_variables()
    {
        $secure = utils::get_server_value( 'HTTPS', '');
        if( $secure && ! in_array( strtolower( $secure ), array( 'off', 'no' ) ) )
        {
            $secure = true;
        }
        else
        {
            $secure = false;
        }

        $url = $secure ? 'https' : 'http';

        // Get domain portion
        $url .= '://'. utils::get_server_value( 'HTTP_HOST', utils::get_server_value( 'SERVER_NAME' ) );

        // Get path to script
        $url .= utils::get_server_value( 'REQUEST_URI', '' );

        // Add path info, if any
        $url .= utils::get_server_value( 'PATH_INFO', '' );

        // Add query string, if any (some servers include a ?, some don't)
        $qs = utils::get_server_value( 'QUERY_STRING' );
        if( $qs )
        {
            $url .= '?' . ltrim( $qs, '?' );
        }

        return $url;
    }

    public static function local_cache_filename_from_url( $url = null )
    {
        if( ! $url )
        {
            $url = self::create_url_from_server_variables();
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
