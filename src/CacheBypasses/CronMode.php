<?php

namespace Vendi\Cache\CacheBypasses;

use Vendi\Cache\Maestro;

final class CronMode extends AbstractCacheBypassWithConstantAndFunction
{
    public function __construct(Maestro $maestro)
    {
        parent::__construct($maestro, 'DOING_CRON');
    }

    public function test_specific_function_and_log_failure()
    {
        //This function is defined in wp-includes/load.php and is guaranteed to
        //exist as long as WP exists.
        if (wp_doing_cron()) {
            $this->log_request_as_not_cacheable_because_function_returned_value('wp_doing_cron', true);
            return false;
        }
        return true;
    }
}
