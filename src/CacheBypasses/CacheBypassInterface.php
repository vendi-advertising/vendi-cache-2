<?php

namespace Vendi\Cache\CacheBypasses;

use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

interface CacheBypassInterface
{
    public function __construct( Maestro $maestro );

    /**
     * [get_maestro description]
     * @return Maestro
     */
    public function get_maestro();

    /**
     * [get_cache_settings description]
     * @return Secretary
     */
    public function get_cache_settings();

    public function get_url( );

    public function get_query_string( );

    public function get_method( );

    public function get_path_url( );

    public function is_cacheable( );

    public function get_cookies( );
}
