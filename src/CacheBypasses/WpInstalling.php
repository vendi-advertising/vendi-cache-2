<?php

namespace Vendi\Cache\CacheBypasses;

final class WpInstalling extends AbstractCacheBypassWithConstantAndFunction
{
    public function __construct(Maestro $maestro)
    {
        parent::__construct($maestro, 'WP_INSTALLING');
    }

    public function test_specific_function_and_log_failure()
    {
        //This function is defined in wp-includes/load.php and is guaranteed to
        //exist as long as WP exists.
        if (wp_installing()) {
            $this->log_request_as_not_cacheable_because_function_returned_value('wp_installing', true);
            return false;
        }
    }
}
