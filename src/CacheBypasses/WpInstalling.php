<?php

namespace Vendi\Cache\CacheBypasses;

final class WpInstalling extends AbstractCacheBypass
{
    public function is_cacheable( )
    {

        /**
         * This should never happen but... just in case.
         *
         * https://developer.wordpress.org/reference/functions/wp_installing/
         */
        if( function_exists( 'wp_installing' ) && wp_installing() )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Install-mode detected',
                                                    ]
                                            );
            return false;
        }

        return true;
    }
}
