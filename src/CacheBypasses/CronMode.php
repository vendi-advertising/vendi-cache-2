<?php

namespace Vendi\Cache\CacheBypasses;

final class CronMode extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        if( false === $this->is_constant_defined_and_set_to_boolean( 'DOING_CRON', true, false, __( 'Request is cron', 'vendi-cache' ) ) )
        {
            return false;
        }

        if( false === $this->is_function_defined_and_returns_boolean( 'wp_doing_cron', true, false, __( 'Request is cron', 'vendi-cache' ) ) )
        {
            return false;
        }

        return true;
    }
}
