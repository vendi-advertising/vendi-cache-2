<?php

namespace Vendi\Cache\CacheBypasses;

final class LoggedInUser extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        if( function_exists( 'wp_get_current_user' ) )
        {
            $user = wp_get_current_user();
            if( $user->exists() )
            {
                $this->log_request_as_not_cacheable(
                                                        [
                                                            'reason' => 'Logged in user',
                                                        ]
                                                );
                return false;
            }
        }

        return true;
    }
}
