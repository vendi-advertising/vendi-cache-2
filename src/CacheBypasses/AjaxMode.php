<?php

namespace Vendi\Cache\CacheBypasses;

final class AjaxMode extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        if( false === $this->is_constant_defined_and_set_to_boolean( 'DOING_AJAX', true, false, __( 'Request is AJAX', 'vendi-cache' ) ) )
        {
            return false;
        }

        if( false === $this->is_function_defined_and_returns_boolean( 'wp_doing_ajax', true, false, __( 'Request is AJAX', 'vendi-cache' ) ) )
        {
            return false;
        }

        return true;
    }
}
