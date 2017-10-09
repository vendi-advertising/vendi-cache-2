<?php

add_action(
            'plugins_loaded',
            function()
            {
                //Get our default master
                $maestro = \Vendi\Cache\Maestro::get_default_instance();

                $maestro
                    ->get_logger()
                    ->debug( 'Plugin loading' )
                ;

                $updater = \Vendi\Cache\Maestro
                            ::get_default_instance()
                            ->get_cache_master()
                            ->get_updater()
                        ;

                if( $updater->is_update_required() )
                {
                    $result = $updater->perform_updates();
                }

                $maestro
                    ->get_cache_master()
                    ->setup_caching()
                ;
            }
        );
