<?php

namespace Vendi\Cache;

use Assert\Assertion;
use Symfony\Component\HttpFoundation\Request;

final class CacheBypassTester
{
    private static $_instance;

    private function __construct()
    {
        //NOOP
    }

    public static function get_instance()
    {
        if( ! self::$_instance )
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function test_request( Request $request = null, \Monolog\Logger $logger = null )
    {
        if( null === $request )
        {
            $request = Request::createFromGlobals();
        }

        if( null === $logger )
        {
            $logger = \Vendi\Cache\Logging::get_instance()->get_logger();
        }

        Assertion::isInstanceOf( $request, '\Symfony\Component\HttpFoundation\Request' );
        Assertion::isInstanceOf( $logger, '\Monolog\Logger' );

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
        $exclusion_rule = CacheExclusions::get_instance()->get_exclusion_rule_for_request();
        if( $exclusion_rule )
        {
            \Vendi\Cache\Logging::get_instance()->get_logger()->debug( 'Request not cacheable', [ 'reason' => 'Exclusion rule cound', 'exclusion_rule' => $exclusion_rule ] );
        }

        return true;

    }
}
