<?php

namespace Vendi\Cache;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

use Ramsey\Uuid\Uuid;

use Vendi\Cache\CacheSettings;

final class VendiMonoLoggger extends Logger
{
    /**
     * Used to trace a specific request through the pipeline by the default logger
     * @var string
     */
    private $_request_id;

    public function __construct( CacheSettings $cache_settings )
    {
        //This is used to trace a specific request through the pipeline
        $this->_request_id = Uuid::uuid4()->toString();

        $log_file_abs = $cache_settings->get_log_file_abs();
        $log_dir_abs = dirname( $log_file_abs );

        if( ! is_dir( $log_dir_abs ) )
        {
            $umask = umask( 0 );
            $status = @mkdir( $log_dir_abs, $cache_settings->get_fs_permission_for_log_dir(), true );
            umask( $umask );

            if( ! is_dir( $log_dir_abs ) )
            {
                throw new \Exception( 'Could not create directory for logging' );
            }
        }

        //Bind to log file
        $stream = new StreamHandler(
                                        $log_file_abs,
                                        $cache_settings->get_logging_level(),

                                        //Bubble
                                        true,

                                        $cache_settings->get_fs_permission_for_log_file()
                                );

        //Custom formatter that puts the request ID in the front as the second
        //variable
        $output = "[%datetime%] [%context.request_id%] [%level_name%]: %message% %context% %extra%\n";
        $formatter = new LineFormatter( $output, null, false, true );
        $stream->setFormatter( $formatter );

        //
        parent::__construct( 'vendi-cache' );
        $this->pushHandler( $stream );

        //Copy to local so that we can close over it in the anonymous func
        $request_id = $this->_request_id;

        //We want to always append the current request's ID for tracing
        $this->pushProcessor(
                                function ($record) use ( $request_id )
                                {
                                    $record['context']['request_id'] = $request_id;
                                    return $record;
                                }
                            );
    }
}
