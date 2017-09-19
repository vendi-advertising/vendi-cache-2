<?php

namespace Vendi\Cache;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

final class Auditing
{
    private static $_instance;

    private function __construct()
    {

    }

    public function get_instance()
    {
        if( ! self::$_instance )
        {
            new self();
        }

        return self::$_instance;
    }

    public function audit_url_requested( $url )
    {

    }

    public function audit_cache_disabled_for_request( $url, $reason )
    {

    }

    public function audit_request_written_to_disk( $url, $bytes )
    {

    }
}
