<?php declare(strict_types=1);
namespace Vendi\Cache;

use Vendi\Cache\Maestro;

final class ErrorHandler
{
    private $_maestro;

    public function __construct(Maestro $maestro)
    {
        $this->_maestro = $maestro;
    }

    public function get_maestro()
    {
        return $this->_maestro;
    }

    /**
     * Set a global constant if an exception occurs and return exception handling
     * back to original handler.
     *
     * @since  1.1.5
     *
     * @param  \Exception $exception The exception that occurred.
     */
    public function handle_exception($exception)
    {
        $this
            ->get_maestro()
            ->get_logger()
            ->error(
                        __('An exception was detected, disabling caching for this request', 'vendi-cache'),
                        [
                            'exception' => $exception,
                        ]
                    )
            ;

        if (! defined('VENDI_CACHE_PHP_ERROR')) {
            define('VENDI_CACHE_PHP_ERROR', true);
        }

        //Pass this exception back to the default handler
        global $vendi_cache_old_exception_handler;
        if ($vendi_cache_old_exception_handler && is_callable($vendi_cache_old_exception_handler)) {
            $vendi_cache_old_exception_handler($exception);
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
    public function handle_error($errno, $errstr, $errfile = null, $errline = null, $errcontext = null)
    {
        $this
            ->get_maestro()
            ->get_logger()
            ->error(
                        __('An error was detected, disabling caching for this request', 'vendi-cache'),
                        [
                            'errno'      => $errno,
                            'errstr'     => $errstr,
                            'errfile'    => $errfile,
                            'errline'    => $errline,
                            'errcontext' => $errcontext,
                        ]
                    )
            ;


        //Maybe?
        // if( E_WARNING === $code )
        // {
        //     return true;
        // }
        if (! defined('VENDI_CACHE_PHP_ERROR')) {
            define('VENDI_CACHE_PHP_ERROR', true);
        }
        // if( false )
        // {
        //     echo $errstr;
        //     echo "<br />\n";
        //     echo $errfile;
        //     echo "<br />\n";
        //     echo $errline;
        // }
        //False means that we're not going to handle the exception here
        return false;
    }
}
