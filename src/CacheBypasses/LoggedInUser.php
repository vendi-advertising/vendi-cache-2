<?php

namespace Vendi\Cache\CacheBypasses;

final class LoggedInUser extends AbstractCacheBypass
{
    public function is_cacheable()
    {
        $settings = $this->get_secretary();

        if (!function_exists('wp_get_current_user')) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Required function wp_get_current_user not found',
                                                    ]
            );
            return false;
        }

        $user = wp_get_current_user();
        if ($user instanceof \WP_User && $user->exists()) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Logged in user',
                                                        'user'   => '$user',
                                                    ]
                                            );
        }

        return true;
    }
}
