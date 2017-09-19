<?php

namespace Vendi\Cache\CacheBypasses;

final class CronMode extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        if( ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Request in a cron',
                                                    ]
                                            );
            return false;
        }

        return true;
    }
}
