<?php declare(strict_types=1);
namespace Vendi\Cache;

use Assert\Assertion;

final class CacheBypassTester
{
    private $_maestro = null;

    public function __construct(Maestro $maestro)
    {
        $this->_maestro = $maestro;
    }

    /**
     * [get_maestro description].
     * @return Maestro
     */
    public function get_maestro()
    {
        return $this->_maestro;
    }

    public function is_resource_not_cacheable()
    {
        $maestro  = $this->get_maestro();
        $logger   = $this->get_maestro()->get_logger();

        Assertion::isInstanceOf($maestro, 'Vendi\Cache\Maestro');
        Assertion::isInstanceOf($logger, 'Psr\Log\LoggerInterface');

        $tests = [
                    //Simple constants
                    'XmlRpcMode',
                    'RestRequestMode',
                    'LegacyConstants',

                    'MaintenanceMode',
                    'LoggedInUser',

                    'PhpError',
                    'WpInstalling',
                    'CronMode',
                    'AjaxMode',
                    'WpCorePage',
                    'HttpRequestMethod',
                    'QueryString',
                    'WpCookies',
            ];

        $logger->debug('Starting bypass tests');

        $root_namespace = __NAMESPACE__;
        $test_namespace = 'CacheBypasses';

        foreach ($tests as $test) {
            $class = "$root_namespace\\$test_namespace\\$test";

            $t = new $class($maestro);
            $result = $t->is_resource_not_cacheable();

            $logger->debug(
                                'Single test run',
                                [
                                    'test'                      => $test,
                                    'is_resource_not_cacheable' => $result,
                                ]
                        );

            if ($result) {
                return true;
            }
        }

        $logger->debug('All tests reported resource cacheable.');


        //TODO: HTTPS Check?

        //TODO: Check for trailing slash?

        //TODO: Not actually built
        // $exclusion_rule = $settings->get_exclusion_rule_for_request();
        // if( $exclusion_rule )
        // {
        //     $logger->debug( 'Request not cacheable', [ 'reason' => 'Exclusion rule cound', 'exclusion_rule' => $exclusion_rule ] );
        // }

        return false;
    }
}
