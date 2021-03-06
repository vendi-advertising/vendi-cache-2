<?php declare(strict_types=1);
namespace Vendi\Cache\CacheBypasses;

final class LegacyConstants extends AbstractCacheBypass
{
    public function is_resource_not_cacheable()
    {
        $settings = $this->get_secretary();

        $legacy_cache_constants = [
                                    'WFDONOTCACHE',
                                    'DONOTCACHEPAGE',
                                    'DONOTCACHEDB',
                                    'DONOTCACHEOBJECT',
                                ];

        foreach ($legacy_cache_constants as $constant) {
            if ($settings->is_constant_defined($constant)) {
                $this->log_request_as_not_cacheable(
                                                        [
                                                            'reason'   => 'Legacy constant found',
                                                            'constant' => $constant,
                                                        ]
                                                );
                return true;
            }
        }

        return false;
    }
}
