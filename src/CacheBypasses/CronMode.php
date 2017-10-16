<?php

namespace Vendi\Cache\CacheBypasses;

final class CronMode extends AbstractCacheBypass
{
    public function is_cacheable()
    {
        if (false === $this->is_cacheable_because_fatal_constant_not_defined_or_is_but_set_to_false('DOING_CRON', __('Request is cron', 'vendi-cache'))) {
            return false;
        }

        if (false === $this->is_cacheable_because_required_function_defined_and_returned_false('wp_doing_cron', __('Request is cron', 'vendi-cache'))) {
            return false;
        }

        return true;
    }
}
