<?php

namespace Vendi\Cache;

use Assert\Assertion;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\{Maestro, CacheSettingsInterface, CacheMaster, VendiMonoLoggger};

final class CacheBypassTester
{
    private $_maestro = null;

    public function __construct( Maestro $maestro )
    {
        $this->_maestro = $maestro;
    }

    public function get_maestro() : Maestro
    {
        return $this->_maestro;
    }

    public function get_logger() : Logger
    {
        return $this->get_maestro()->get_logger();
    }

    public function get_cache_settings() : CacheSettingsInterface
    {
        return $this->get_maestro()->get_cache_settings();
    }

    public function get_request() : Request
    {
        return $this->get_maestro()->get_request();
    }

    public function test_request( )
    {
        $request  = $this->get_request();
        $logger   = $this->get_logger();
        $settings = $this->get_cache_settings();

        Assertion::isInstanceOf( $request, Request::class );
        Assertion::isInstanceOf( $logger, Logger::class );
        Assertion::isInstanceOf( $settings, CacheSettingsInterface::class );

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

        $root_namespace = __NAMESPACE__;
        $test_namespace = 'CacheBypasses';

        foreach( $tests as $test )
        {
            $class = "$root_namespace\\$test_namespace\\$test";
            $t = new $class( $request, $logger );
            if( ! $t->is_cacheable() )
            {
                return false;
            }
        }


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