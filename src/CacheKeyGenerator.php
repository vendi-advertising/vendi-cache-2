<?php

namespace Vendi\Cache;

use Assert\Assertion;
use Symfony\Component\HttpFoundation\Request;

class CacheKeyGenerator
{

    private static $_urls_to_files = [];

    private static $_urls_to_files_cache_lookups = [];

    public static function get_mapping_of_urls_to_files() : array
    {
        //Prove that our property is always an array
        Assertion::isArray( self::$_urls_to_files );

        return self::$_urls_to_files;
    }

    public static function get_cache_lookup_counts_for_url( $url )
    {
        //A non-null string is required
        Assertion::string( $url );
        Assertion::notEmpty( $url );

        if( ! array_key_exists( $url, self::$_urls_to_files_cache_lookups ) )
        {
            return -1;
        }

        //The cache lookup should always be an int
        Assertion::integer( self::$_urls_to_files_cache_lookups[ $url ] );

        return self::$_urls_to_files_cache_lookups[ $url ];
    }

    public static function sanitize_host_for_cache_filename( $host )
    {
        //A non-null string is required
        Assertion::string( $host );
        Assertion::notEmpty( $host );

        $ret = preg_replace( '/[^a-zA-Z0-9\-\.]+/', '', $host );

        //The replacement must give us something to work with
        Assertion::notEmpty( $host );

        return $ret;
    }

    /**
     * Create a URL
     *
     * @return
     */

    /**
     * Create a string URL based on the current PHP request.
     *
     * NOTE: $globals should only ever be used for testing, never in production.
     *
     * @param  array Optional. An array of arrays with any/all keys of GET, POST,
     *                         COOKIE or SERVER to override server defaults.
     * @return string          An absolute URL based on the current request.
     */
    public static function create_url_from_server_variables( array $globals = null )
    {
        if( null !== $globals )
        {
            $local_GET    = array_key_exists( 'GET', $globals )    && is_array( $globals[ 'GET' ] )    ? $globals[ 'GET' ] : array();
            $local_POST   = array_key_exists( 'POST', $globals )   && is_array( $globals[ 'POST' ] )   ? $globals[ 'POST' ] : array();
            $local_COOKIE = array_key_exists( 'COOKIE', $globals ) && is_array( $globals[ 'COOKIE' ] ) ? $globals[ 'COOKIE' ] : array();
            $local_SERVER = array_key_exists( 'SERVER', $globals ) && is_array( $globals[ 'SERVER' ] ) ? $globals[ 'SERVER' ] : array();
            $req = new \Symfony\Component\HttpFoundation\Request( $local_GET, $local_POST, array(), $local_COOKIE, array(), $local_SERVER, null );
        }
        else
        {
            $req = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        }

        $ret = $req->getUri();

        //Make sure we have a non-null string for the URL
        Assertion::string( $ret );
        Assertion::notEmpty( $ret );

        return $ret;
    }

    /**
     * Return a file-system-local file name based on the given URL or global
     * HTTP Request variables.
     *
     * @param  string|Array $url_or_globals Either a string representing the fully-qualified URL or an array of HTTP
     *                                      server GLOBALS to construct from.
     * @return string                       A path to the local file for the given request.
     */
    public static function local_cache_filename_from_url( $url_or_globals = null )
    {
        //This should be either null, an array or a string
        Assertion::satisfy(
                            $url_or_globals,
                            function( $value )
                            {
                                return is_null( $value ) || is_string( $value ) || is_array( $value );
                            }
        );

        if( is_array( $url_or_globals ) )
        {
            $url = self::create_url_from_server_variables( $url_or_globals );
        }
        elseif( ! $url_or_globals )
        {
            $url = self::create_url_from_server_variables();
        }
        elseif( is_string( $url_or_globals ) )
        {
            $url = $url_or_globals;
        }

        //See if we've previously determined the file for this URL
        if( array_key_exists( $url, self::$_urls_to_files ) )
        {
            //Increment a shared global counter, used for testing
            self::$_urls_to_files_cache_lookups[ $url ] += 1;

            $ret = self::$_urls_to_files[ $url ];

            Assertion::string( $ret );
            Assertion::notEmpty( $ret );

            return $ret;
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
        Assertion::string( $path );
        Assertion::notEmpty( $path );

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

        Assertion::notEmpty( $path );
        return $path;
    }
}
