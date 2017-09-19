<?php

namespace Vendi\Cache\CacheBypasses;

final class AjaxMode extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        if( ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Request is AJAX',
                                                    ]
                                            );
            return false;
        }

        return true;
    }
}
