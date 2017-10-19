<?php

namespace Vendi\Cache\CacheBypasses;

use Vendi\Cache\Maestro;

final class AjaxMode extends AbstractCacheBypassWithConstantAndFunction
{
    public function __construct(Maestro $maestro)
    {
        parent::__construct($maestro, 'DOING_AJAX');
    }

    public function test_specific_function_and_log_failure()
    {
        //This function is defined in wp-includes/load.php and is guaranteed to
        //exist as long as WP exists.
        if (wp_doing_ajax()) {
            $this->log_request_as_not_cacheable_because_function_returned_value('wp_doing_ajax', true);
            return false;
        }
        return true;
    }
}
