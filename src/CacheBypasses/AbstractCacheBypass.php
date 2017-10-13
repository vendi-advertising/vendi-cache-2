<?php

namespace Vendi\Cache\CacheBypasses;

use Assert\Assertion;
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

    /**
     * Get the Maestro associated with the current request.
     * @return Maestro
     */
    final public function get_maestro()
    {
        return $this->_maestro;
    }

    /**
     * Get the cache settings associated with the current request.
     * @return CacheSettingsInterface
     */
    final public function get_cache_settings()
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

    final public function is_cacheable_because_required_function_defined_and_returned_false( $name, $failure_reason )
    {
        //Sanity check params
        Assertion::notEmpty( $name );
        Assertion::string(   $name );
        Assertion::notEmpty( $failure_reason );
        Assertion::string(   $failure_reason );

        //Get our settings
        $settings = $this->get_cache_settings();

        //This is a required function. If it doesn't exist then we are in a
        //strange state and caching should be disabled.
        if( ! $settings->is_function_defined( $name ) )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => "Required function $name not defined",
                                                    ]
                                            );

            return false;
        }

        //The function must always return a boolean
        $result = (bool) $settings->get_function_value( $name );

        //If the function returns true then someone else in the pipeline
        //initiated something special and we don't want to cache this request.
        if( $result )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => "Required function $name return true",
                                                    ]
                                            );

            return false;
        }

        //Nothing special happened, flag the request as cacheable as far as this
        //method is concerned.
        return true;
    }

    final public function is_cacheable_because_fatal_constant_not_defined_or_is_but_set_to_false( $name, $failure_reason )
    {
        //Sanity check params
        Assertion::notEmpty( $name );
        Assertion::string(   $name );
        Assertion::notEmpty( $failure_reason );
        Assertion::string(   $failure_reason );

        $settings = $this->get_cache_settings();

        //We're looking for hard-stop constants. If the constant doesn't exist
        //then assume that we can cache this resource.
        if( ! $settings->is_constant_defined( $name ) )
        {
            return true;
        }

        //Constants are assumed to be boolean
        $result = $settings->get_constant_value( $name );
        $result = (bool) $settings->get_constant_value( $name );

        //The constant _IS_ defined but set to a false-like value. Super weird
        //and I'm pretty sure this should never happen but still technically
        //valid as far as PHP is concerned.
        if( false === $result )
        {
            $this
                ->get_maestro()
                ->get_logger()
                ->debug(
                            'Strange state - constant set to false',
                            [
                                'constant' => $name,
                            ]
                    );
            return true;
        }

        $this->log_request_as_not_cacheable(
                                                [
                                                    'reason' => $failure_reason,
                                                    'extra'  => "Constant $name is false",
                                                ]
                                        );

        return false;
    }
}
