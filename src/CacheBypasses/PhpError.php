<?php

namespace Vendi\Cache\CacheBypasses;

final class PhpError extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        $settings = $this->get_secretary();

        if( $settings->is_constant_defined( 'VENDI_CACHE_PHP_ERROR' ) )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason'   => 'Explicit constant found',
                                                        'constant' => 'VENDI_CACHE_PHP_ERROR',
                                                    ]
                                            );
            return false;
        }

        return true;
    }
}
