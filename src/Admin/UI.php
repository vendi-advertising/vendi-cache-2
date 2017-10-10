<?php

namespace Vendi\Cache\Admin;

final class UI
{

    const URL_SLUG = 'vendi-cache-2-settings';

    private $_instance;

    private function __construct()
    {

    }

    public static function get_instance()
    {
        if( ! self::$_instance instanceof self )
        {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public static function route_request()
    {
        self::get_instance()->show_page();
    }

    public function show_page()
    {
        require VENDI_CACHE_DIR . '/templates/cache-settings.php';
    }
}
