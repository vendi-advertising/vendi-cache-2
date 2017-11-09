<?php declare(strict_types=1);
namespace Vendi\Cache;

final class CacheExclusionTester
{
    private $_maestro = null;

    public function __construct(Maestro $maestro)
    {
        $this->_maestro = $maestro;
    }

    public function is_resource_not_cacheable()
    {
        $maestro  = $this->get_maestro();
        $logger   = $this->get_maestro()->get_logger();

        $possible_sources = [
                                'url'               => 'UrlSource',
                                'user-agent'        => 'UserAgentSource',
                                'cookie-name'       => 'CookieNameSourceSource',
        ];

        $possible_comparators = [
                                'matches-exactly'   => 'MatchesExactly',
                                'contains'          => 'Contains',
                                'ends-with'         => 'EndsWith',
                                'starts-with'       => 'StartsWith',
        ];

        $logger->debug('Starting exclusion tests');

        $root_namespace       = __NAMESPACE__;
        $exclusion_namespace  = "$root_namespace\\CacheExclusions";
        $source_namespace     = "$exclusion_namespace\\Sources";
        $comparator_namespace = "$exclusion_namespace\\Comparators";

        // foreach ($tests as $test) {
        //     $class = "$root_namespace\\$test_namespace\\$test";

        //     $t = new $class($maestro);
        //     $result = $t->is_resource_not_cacheable();

        //     if ($debug && $result) {
        //         dump($class);
        //     }

        //     $logger->debug(
        //                         'Single test run',
        //                         [
        //                             'test'                      => $test,
        //                             'is_resource_not_cacheable' => $result,
        //                         ]
        //                 );

        //     if ($result) {
        //         return true;
        //     }
        // }

        // $logger->debug('All tests reported resource cacheable.');


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
