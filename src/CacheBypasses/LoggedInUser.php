<?php

namespace Vendi\Cache\CacheBypasses;

final class LoggedInUser extends AbstractCacheBypass
{
    public function is_cacheable()
    {
        if (!function_exists('get_current_user_id')) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Required function get_current_user_id not found',
                                                    ]
            );
            return false;
        }

        if (0 !== get_current_user_id()) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason'  => 'Logged in user',
                                                        'user_id' => '$user_id',
                                                    ]
                                            );
            return false;
        }

        return true;
    }
}
