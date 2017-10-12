<?php

namespace Vendi\Cache\CacheBypasses;

final class AjaxMode extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        if( false === $this->is_cacheable_because_fatal_constant_not_defined_or_set_to_true( 'DOING_AJAX', __( 'Request is AJAX', 'vendi-cache' ) ) )
        {
            return false;
        }

        if( false === $this->is_cacheable_because_required_function_defined_and_returned_false( 'wp_doing_ajax', __( 'Request is AJAX', 'vendi-cache' ) ) )
        {
            return false;
        }

        return true;
    }
}
