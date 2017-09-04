<?php

namespace Vendi\Cache;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logging
{
    private static $_instance;

    private function __construct()
    {

        $output = "[%datetime%] [%context.request_id%] %channel%.%level_name%: %message% %context% %extra%\n";

        $formatter = new LineFormatter( $output, null, false, true );

        $stream = new StreamHandler( VENDI_CACHE_LOG_FILE_ABS );
        $stream->setFormatter( $formatter );

        self::$_instance = new \Monolog\Logger( 'vendi-cache' );
        self::$_instance->pushHandler( $stream );

        //This is used to trace a specific request through the pipeline
        define( 'VENDI_CACHE_REQUEST_ID', \Ramsey\Uuid\Uuid::uuid4() );
    }

    public function get_instance()
    {
        if( ! self::$_instance )
        {
            new self();
        }

        return self::$_instance;
    }
}
