<?php

namespace Vendi\Cache\CacheBypasses;

use Vendi\Cache\CacheSettingsInterface;
use Vendi\Cache\Maestro;

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
        $request = $this->get_maestro()->get_request();
        return $request->getBaseUrl() . $request->getPathInfo();
    }

    final public function get_cookies( )
    {
        return $this->get_maestro()->get_request()->cookies;
    }

    final public function log_request_as_not_cacheable( array $args )
    {
        $this->get_maestro()->get_logger()->debug( 'Request not cacheable', $args );
    }

    final public function is_function_defined_and_returns_boolean( $name, $boolean, $is_cacheable_if_defined_and_boolean, $failure_reason )
    {
        $settings = $this->get_cache_settings();

        if( ! $settings->is_function_defined( $name ) )
        {
            return ! $is_cacheable_if_defined_and_boolean;
        }

        if( $boolean === $settings->get_function_value( $name ) )
        {
            return $is_cacheable_if_defined_and_boolean;
        }

        $this->log_request_as_not_cacheable(
                                                [
                                                    'reason' => $failure_reason,
                                                    'extra'  => "Function $name returned $boolean",
                                                ]
                                        );
        return ! $is_cacheable_if_defined_and_boolean;
    }

    final public function is_constant_defined_and_set_to_boolean( $name, $boolean, $is_cacheable_if_defined_and_boolean, $failure_reason )
    {
        $settings = $this->get_cache_settings();

        if( ! $settings->is_constant_defined( $name ) )
        {
            return ! $is_cacheable_if_defined_and_boolean;
        }

        if( $boolean === $settings->get_constant_value( $name ) )
        {
            return $is_cacheable_if_defined_and_boolean;
        }

        $this->log_request_as_not_cacheable(
                                                [
                                                    'reason' => $failure_reason,
                                                    'extra'  => "Constant $name is $boolean",
                                                ]
                                        );

        return ! $is_cacheable_if_defined_and_boolean;
    }
}
