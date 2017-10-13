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
        if( false === $this->is_cacheable_because_fatal_constant_not_defined_or_is_but_set_to_false( 'WP_INSTALLING', __( 'Request is cron', 'vendi-cache' ) ) )
        {
            return false;
        }

        if( false === $this->is_cacheable_because_required_function_defined_and_returned_false( 'wp_installing', __( 'Request is cron', 'vendi-cache' ) ) )
        {
            return false;
        }

        return true;
    }
}
