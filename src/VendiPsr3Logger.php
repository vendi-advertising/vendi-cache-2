<?php declare(strict_types=1);
namespace Vendi\Cache;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use MySQLHandler\MySQLHandler;

use Ramsey\Uuid\Uuid;

final class VendiPsr3Logger extends Logger
{
    /**
     * Used to trace a specific request through the pipeline by the default logger.
     * @var string
     */
    private $_request_id;

    public function _generate_new_request_id()
    {
        //This is used to trace a specific request through the pipeline
        $this->_request_id = Uuid::uuid4()->toString();
    }

    public function _maybe_create_log_dir(Secretary $cache_settings)
    {
        $log_file_abs = $cache_settings->get_log_file_abs();
        $log_dir_abs = dirname($log_file_abs);

        if (! is_dir($log_dir_abs)) {
            $umask = umask(0);
            $status = @mkdir($log_dir_abs, $cache_settings->get_fs_permission_for_log_dir(), true);
            umask($umask);

            if (! is_dir($log_dir_abs)) {
                throw new \Exception('Could not create directory for logging');
            }
        }
    }

    public function _create_and_push_stream_handler(Secretary $cache_settings)
    {
        $log_file_abs = $cache_settings->get_log_file_abs();
        $log_dir_abs = dirname($log_file_abs);

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
        $formatter = new LineFormatter($output, null, false, true);
        $stream->setFormatter($formatter);

        $this->pushHandler($stream);
    }

    public function __construct(Secretary $cache_settings)
    {
        parent::__construct('vendi-cache');

        $this->_generate_new_request_id();
        $this->_maybe_create_log_dir($cache_settings);

        $this->_create_and_push_stream_handler($cache_settings);

        global $wpdb;

        $pdo = new \PDO(sprintf('mysql:host=%2$s;dbname=%1$s', DB_NAME, DB_HOST), DB_USER, DB_PASSWORD);
        $mySQLHandler = new MySQLHandler($pdo, $wpdb->get_blog_prefix() . 'vendi_cache_log', ['request_id']);
        $this->pushHandler($mySQLHandler);

        //We want to always append the current request's ID for tracing
        $this->pushProcessor(
                                function ($record) {
                                    $record['context']['request_id'] = $this->_request_id;
                                    return $record;
                                }
                            );
    }
}
