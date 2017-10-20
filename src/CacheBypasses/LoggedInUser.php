<?php

namespace Vendi\Cache\CacheBypasses;

final class LoggedInUser extends AbstractCacheBypass
{
    public function is_resource_not_cacheable()
    {
        //I have no way to test this. There's really no way for this method to
        //get invoked without the function existing (since we load everything in
        //plugins_loaded) but in the off-chance that we get invoked strangely,
        //maybe through WP-CLI changes, I'm still including it.
        if (!function_exists('get_current_user_id')) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Required function get_current_user_id not found',
                                                    ]
            );
            return true;
        }

        if (0 !== get_current_user_id()) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason'  => 'Logged in user',
                                                        'user_id' => '$user_id',
                                                    ]
                                            );
            return true;
        }

        return false;
    }
}
