<?php

namespace Vendi\Cache;

use Assert\Assertion;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\CacheMaster;
use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;
use Vendi\Cache\VendiMonoLoggger;

final class CacheBypassTester
{
    private $_maestro = null;

    public function __construct( Maestro $maestro )
    {
        $this->_maestro = $maestro;
    }

    /**
     * [get_maestro description]
     * @return Maestro
     */
    public function get_maestro()
    {
        return $this->_maestro;
    }

    /**
     * [get_logger description]
     * @return Logger
     */
    public function get_logger()
    {
        return $this->get_maestro()->get_logger();
    }

    /**
     * [get_cache_settings description]
     * @return Secretary
     */
    public function get_cache_settings()
    {
        return $this->get_maestro()->get_cache_settings();
    }

    /**
     * [get_request description]
     * @return Request
     */
    public function get_request()
    {
        return $this->get_maestro()->get_request();
    }

    public function test_request( )
    {
        $maestro  = $this->get_maestro();
        $request  = $this->get_request();
        $logger   = $this->get_logger();
        $settings = $this->get_cache_settings();

        Assertion::isInstanceOf( $maestro,  'Vendi\Cache\Maestro' );
        Assertion::isInstanceOf( $request,  'Symfony\Component\HttpFoundation\Request' );
        Assertion::isInstanceOf( $logger,   'Monolog\Logger' );
        Assertion::isInstanceOf( $settings, 'Vendi\Cache\Secretary' );

        $tests = [
                    'MaintenanceMode',
                    'LoggedInUser',
                    'LegacyConstants',
                    'PhpError',
                    'WpInstalling',
                    'CronMode',
                    'AjaxMode',
                    'WpCorePage',
                    'HttpRequestMethod',
                    'QueryString',
                    'WpCookies',
            ];

        $logger->debug( 'Starting tests' );

        $root_namespace = __NAMESPACE__;
        $test_namespace = 'CacheBypasses';

        foreach( $tests as $test )
        {
            $class = "$root_namespace\\$test_namespace\\$test";

            $t = new $class( $maestro );
            $result = $t->is_cacheable();

            $logger->debug(
                                'Single test run',
                                [
                                    'test'          => $test,
                                    'is_cacheable'  => $result,
                                ]
                        );

            if( false === $result )
            {
                return false;
            }
        }

        $logger->debug( 'All tests reported resourece cacheable.' );


        //TODO: HTTPS Check?

        //TODO: Check for trailing slash?

        //TODO: Not actually built
        // $exclusion_rule = $settings->get_exclusion_rule_for_request();
        // if( $exclusion_rule )
        // {
        //     $logger->debug( 'Request not cacheable', [ 'reason' => 'Exclusion rule cound', 'exclusion_rule' => $exclusion_rule ] );
        // }

        return true;

    }
}
