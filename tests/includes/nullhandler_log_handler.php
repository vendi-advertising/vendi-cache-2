<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Monolog\Handler\NullHandler;

final class nullhandler_log_handler extends NullHandler
{
    private $handle_function;

    public function __construct(callable $handle_function = null)
    {
        $this->handle_function = $handle_function;
    }

    public function handle(array $record)
    {
        if ($this->handle_function) {
            $func = $this->handle_function;
            $func($record);
        }
    }
}
