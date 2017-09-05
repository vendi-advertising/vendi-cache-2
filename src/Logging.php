<?php

namespace Vendi\Cache;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logging
{
    private static $_logger;

    private $_request_id;

    private function __construct()
    {
        //This is used to trace a specific request through the pipeline
        $this->_request_id = \Ramsey\Uuid\Uuid::uuid4()->toString();

        //Bind to log file
        $stream = new StreamHandler( VENDI_CACHE_LOG_FILE_ABS );

        //Custom formatter that puts the request ID in the front as the second
        //variable
        $output = "[%datetime%] [%context.request_id%] [%level_name%]: %message% %context% %extra%\n";
        $formatter = new LineFormatter( $output, null, false, true );
        $stream->setFormatter( $formatter );

        //
        self::$_logger = new \Monolog\Logger( 'vendi-cache' );
        self::$_logger->pushHandler( $stream );

        //Copy to local so that we can close over it in the anonymous func
        $request_id = $this->_request_id;

        //We want to always append the current request's ID for tracing
        self::$_logger->pushProcessor(
                                            function ($record) use ( $request_id )
                                            {
                                                $record['context']['request_id'] = $request_id;
                                                return $record;
                                            }
                                        );
    }

    public static function get_instance()
    {
        if( ! self::$_logger )
        {
            new self();
        }

        return self::$_logger;
    }
}

/*
https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md

//Logs with an arbitrary level
\Vendi\Cache\Logging::get_instance()->log( '' );

//Detailed debug information
\Vendi\Cache\Logging::get_instance()->debug( '' );

//Interesting events
\Vendi\Cache\Logging::get_instance()->info( '' );

//Normal but significant events
\Vendi\Cache\Logging::get_instance()->notice( '' );

//Exceptional occurrences that are not errors
\Vendi\Cache\Logging::get_instance()->warning( '' );

//Runtime errors that do not require immediate action but
//should typically be logged and monitored
\Vendi\Cache\Logging::get_instance()->error( '' );

//Critical conditions
\Vendi\Cache\Logging::get_instance()->critical( '' );

//Action must be taken immediately
\Vendi\Cache\Logging::get_instance()->alert( '' );

//System is unusable
\Vendi\Cache\Logging::get_instance()->emergency( '' );
*/
