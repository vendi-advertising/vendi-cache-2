<?php

namespace Vendi\Cache\CacheBypasses;

final class LegacyConstants extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        $legacy_cache_constants = [
                                    'WFDONOTCACHE',
                                    'DONOTCACHEPAGE',
                                    'DONOTCACHEDB',
                                    'DONOTCACHEOBJECT',
                                ];

        foreach( $legacy_cache_constants as $constant )
        {
            if( defined( $constant ) )
            {
                $this->log_request_as_not_cacheable(
                                                        [
                                                            'reason'   => 'Legacy constant found',
                                                            'constant' => $constant,
                                                        ]
                                                );
                return false;
            }
        }

        return true;
    }
}
