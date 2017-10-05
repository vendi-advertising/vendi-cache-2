<?php

namespace Vendi\Cache\CacheBypasses;

use Vendi\Cache\{CacheSettingsInterface, Maestro};

abstract class AbstractCacheBypass implements CacheBypassInterface
{
    private $_request;

    private $_logger;

    private $_maestro;

    final function __construct( Maestro $maestro )
    {
        $this->_maestro = $maestro;
    }

    final public function get_maestro() : Maestro
    {
        return $this->_maestro;
    }

    final public function get_cache_settings() : CacheSettingsInterface
    {
        return $this->get_maestro()->get_cache_settings();
    }

    final public function get_url( )
    {
        return $this->get_maestro()->get_request()->getUri();
    }

    final public function get_query_string( )
    {
        return $this->get_maestro()->get_request()->getQueryString();
    }

    final public function get_method( )
    {
        return $this->get_maestro()->get_request()->getMethod();
    }

    final public function get_path_url( )
    {
        //TODO: I'm not 100% sure this is right
        //https://github.com/symfony/http-foundation/blob/3.4/Request.php#L982
        return $this->get_maestro()->get_request()->getBaseUrl();
    }

    final public function get_cookies( )
    {
        return $this->get_maestro()->get_request()->cookies;
    }

    final public function log_request_as_not_cacheable( array $args )
    {
        $this->get_maestro()->get_logger()->debug( 'Request not cacheable', $args );
    }
}
