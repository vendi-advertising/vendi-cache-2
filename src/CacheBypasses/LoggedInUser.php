<?php

namespace Vendi\Cache\CacheBypasses;

final class LoggedInUser extends AbstractCacheBypass
{
    public function is_cacheable()
    {
        $settings = $this->get_secretary();

        if ($settings->is_function_defined('wp_get_current_user')) {
            $user = $settings->get_function_value('wp_get_current_user');
            if ($user instanceof \WP_User && $user->exists()) {
                $this->log_request_as_not_cacheable(
                                                        [
                                                            'reason' => 'Logged in user',
                                                            'user'   => '$user',
                                                        ]
                                                );
                return false;
            }
        }

        return true;
    }
}
