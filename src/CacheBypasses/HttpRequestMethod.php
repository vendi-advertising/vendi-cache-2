<?php declare(strict_types=1);
namespace Vendi\Cache\CacheBypasses;

final class HttpRequestMethod extends AbstractCacheBypass
{
    public function is_resource_not_cacheable()
    {
        $method = $this->get_method();

        if ('GET' !== $method) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Non-GET request received',
                                                        'method' => $method,
                                                    ]
                                            );

            return true;
        }

        return false;
    }
}
