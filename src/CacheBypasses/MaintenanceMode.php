<?php

namespace Vendi\Cache\CacheBypasses;

use Webmozart\PathUtil\Path;

final class MaintenanceMode extends AbstractCacheBypass
{
    public function is_cacheable( )
    {
        $settings = $this->get_secretary();

        if( ! $settings->is_constant_defined( 'ABSPATH' ) )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Maintenance mode',
                                                        'extra'  => 'Missing constant ABSPATH',
                                                    ]
                                            );
            //Do not cache because we don't appear to be in WordPress!!!
            //TODO: Maybe throw here instead?
            return false;
        }

        $abs = $settings->get_constant_value( 'ABSPATH' );
        $test_file = Path::join( $abs, '.maintenance' );

        if( file_exists( $test_file ) )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Maintenance mode',
                                                        'src'    => 'File exists',
                                                        'file'   => $test_file,
                                                    ]
                                            );
            return false;
        }

        if( apply_filters( 'enable_maintenance_mode', false, 0 ) )
        {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Maintenance mode',
                                                        'src'    => 'Filter enable_maintenance_mode returned true',
                                                    ]
                                            );
            return false;
        }

        return true;
    }
}
