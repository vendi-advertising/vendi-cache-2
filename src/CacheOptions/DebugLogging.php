<?php

namespace Vendi\Cache\CacheOptions;

class DebugLogging extends AbstractCacheOption
{
    const LOGGING_OFF = 'off';

    const LOG_TO_DATEBASE = 'database';

    const LOG_TO_FILE = 'file';

    const LOG_TO_DATEBASE_AND_FILE = 'database-and-file';

    public function get_option_type()
    {
        return self::OPTION_TYPE_RADIO;
    }

    public function get_default_value()
    {
        return self::LOGGING_OFF;
    }

    public function get_potential_options()
    {
        return [
                    self::LOGGING_OFF               => __( 'Debug logging off',         'vendi-cache' ),
                    self::LOG_TO_DATEBASE           => __( 'Log to database',           'vendi-cache' ),
                    self::LOG_TO_FILE               => __( 'Log to file',               'vendi-cache' ),
                    self::LOG_TO_DATEBASE_AND_FILE  => __( 'Log to datebase and file',  'vendi-cache' ),
            ];
    }

    public function get_description()
    {
        return __( 'Debug Logging', 'vendi-cache' );
    }

    public function get_storage_name()
    {
        return 'debug-logging';
    }
}
