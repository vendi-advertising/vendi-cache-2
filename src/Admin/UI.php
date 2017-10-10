<?php

namespace Vendi\Cache\Admin;

final class UI
{

    private $_instance;

    private function __construct()
    {

    }

    public static function get_instance()
    {
        if( ! $this->_instance instanceof self )
        {
            $this->_instance = new self;
        }

        return $this->_instance;
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
