<?php declare(strict_types=1);
namespace Vendi\Cache\CacheBypasses;

final class LoggedInUser extends AbstractCacheBypass
{
    public function is_resource_not_cacheable()
    {
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
