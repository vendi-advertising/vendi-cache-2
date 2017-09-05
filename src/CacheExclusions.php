<?php

namespace Vendi\Cache;

final class CacheExclusions
{
    private static $_instance;

    private function __construct()
    {

    }

    public static function get_instance()
    {
        if( ! self::$_instance )
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    //TODO
    public function get_exclusion_rule_for_request( )
    {
        return null;
    }
}
