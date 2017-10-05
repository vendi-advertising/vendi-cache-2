<?php

namespace Vendi\Cache\CacheBypasses;

final class AjaxMode extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        $settings = $this->get_cache_settings();

        if( $settings->is_function_defined( 'wp_doing_ajax' ) )
        {
            if( $settings->get_function_value( 'wp_doing_ajax' ) )
            {
                $this->log_request_as_not_cacheable(
                                                        [
                                                            'reason' => 'Request is AJAX',
                                                            'extra'  => 'Function wp_doing_ajax returned true',
                                                        ]
                                                );
                return false;
            }
        }

        if( $settings->is_constant_defined( 'DOING_AJAX' ) )
        {
            if( $settings->get_constant_value( 'DOING_AJAX' ) )
            {
                $this->log_request_as_not_cacheable(
                                                        [
                                                            'reason' => 'Request is AJAX',
                                                            'extra'  => 'Constant DOING_AJAX defined and true',
                                                        ]
                                                );
                return false;
            }
        }

        return true;
    }
}
