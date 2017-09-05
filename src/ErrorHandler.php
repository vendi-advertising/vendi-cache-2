<?php

namespace Vendi\Cache;

final class ErrorHandler
{
    /**
     * Set a global constant if an exception occurs and return exception handling
     * back to original handler.
     *
     * @since  1.1.5
     *
     * @param  \Exception $exception The exception that occurred.
     */
    public static function handle_exception( $exception )
    {
        \Vendi\Cache\Logging::get_instance()->error( 'An exception was detected, disabling caching for this request', [ 'exception' => $exception ] );

        if( ! defined( 'VENDI_CACHE_PHP_ERROR' ) )
        {
            define( 'VENDI_CACHE_PHP_ERROR', true );
        }

        //Pass this exception back to the default handler
        global $vendi_cache_old_exception_handler;
        if( $vendi_cache_old_exception_handler && is_callable( $vendi_cache_old_exception_handler ) )
        {
            $vendi_cache_old_exception_handler( $exception );
        }
    }

    /**
     * Set a global constant if an error occurs and return error handling
     * back to original handler.
     *
     * See PHP docs for parameters.
     *
     * @since  1.1.5
     */
    public static function handle_error( $errno, $errstr, $errfile = null, $errline = null, $errcontext = null )
    {
        \Vendi\Cache\Logging::get_instance()->error(
                                                        'An error was detected, disabling caching for this request',
                                                        [
                                                            'errno' => $errno,
                                                            'errstr' => $errstr,
                                                            'errfile' => $errfile,
                                                            'errline' => $errline,
                                                            'errcontext' => $errcontext,
                                                        ]
                                                    );

        //Maybe?
        // if( E_WARNING === $code )
        // {
        //     return true;
        // }
        if( ! defined( 'VENDI_CACHE_PHP_ERROR' ) )
        {
            define( 'VENDI_CACHE_PHP_ERROR', true );
        }
        // if( false )
        // {
        //     echo $errstr;
        //     echo "<br />\n";
        //     echo $errfile;
        //     echo "<br />\n";
        //     echo $errline;
        //     die( $errstr );
        // }
        //False means that we're not going to handle the exception here
        return false;
    }
}
