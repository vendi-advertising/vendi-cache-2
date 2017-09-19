<?php

namespace Vendi\Cache\CacheBypasses;

final class MaintenanceMode extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        if( defined( 'ABSPATH' ) && file_exists( ABSPATH . '.maintenance' ) )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Maintenance mode',
                                                        'src'    => 'File exists',
                                                        'file'   => 'ABSPATH + .maintenance',
                                                    ]
                                            );
            return false;
        }

        if( ! function_exists( 'apply_filters' ) || apply_filters( 'enable_maintenance_mode', false, 0 ) )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Maintenance mode',
                                                        'src'    => 'Filter',
                                                    ]
                                            );
            return false;
        }

        return true;
    }
}
