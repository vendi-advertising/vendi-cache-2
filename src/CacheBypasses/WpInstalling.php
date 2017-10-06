<?php

namespace Vendi\Cache\CacheBypasses;

final class WpInstalling extends AbstractCacheBypass
{
        /**
         * This should never happen but... just in case.
         *
         * https://developer.wordpress.org/reference/functions/wp_installing/
         */
    public function is_cacheable( )
    {
        if( false === $this->is_constant_defined_and_set_to_boolean( 'WP_INSTALLING', true, false, __( 'Request is cron', 'vendi-cache' ) ) )
        {
            return false;
        }

        if( false === $this->is_function_defined_and_returns_boolean( 'wp_installing', true, false, __( 'Request is cron', 'vendi-cache' ) ) )
        {
            return false;
        }

        return true;
    }
}
