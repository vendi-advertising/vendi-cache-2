<?php

namespace Vendi\Cache\CacheBypasses;

final class AjaxMode extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        if( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Request is AJAX',
                                                        'extra'  => 'Function wp_doing_ajax returned true',
                                                    ]
                                            );
            return false;
        }

        if( defined( 'DOING_AJAX' ) && DOING_AJAX )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Request is AJAX',
                                                        'extra'  => 'Constant DOING_AJAX defined and true',
                                                    ]
                                            );
            return false;
        }

        return true;
    }
}
