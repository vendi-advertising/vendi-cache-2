<?php

namespace Vendi\Cache\CacheBypasses;

final class HttpRequestMethod extends AbstractCacheBypass
{
    public function is_cacheable()
    {
        $method = $this->get_method();

        if ('GET' !== $method) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Non-GET request received',
                                                        'method' => $method,
                                                    ]
                                            );

            return false;
        }

        return true;
    }
}
