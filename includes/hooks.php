<?php

add_action(
            'plugins_loaded',
            function()
            {
                //Get our default master
                $maestro = \Vendi\Cache\Maestro::get_default_instance();

                //Log the start of bootup
                $maestro
                    ->get_logger()
                    ->debug( 'Plugin loading' )
                ;

                //Get the updater
                $updater = \Vendi\Cache\Maestro
                            ::get_default_instance()
                            ->get_cache_master()
                            ->get_updater()
                        ;

                //Check for updates
                if( $updater->is_update_required() )
                {
                    $result = $updater->perform_updates();
                }

                //Create our admin menu
                add_action(
                            'admin_menu',
                            function() use ( $maestro )
                            {
                                add_submenu_page(
                                                    'options-general.php',
                                                    'Vendi Cache 2',
                                                    'Vendi Cache 2',
                                                    'manage_options',
                                                    \Vendi\Cache\Admin\UI::URL_SLUG,
                                                    function() use ( $maestro )
                                                    {
                                                        $maestro
                                                            ->get_admin_ui()
                                                            ->handle_page_routing()
                                                        ;
                                                    }
                                                );
                            }
                        );

                //Setup caching
                $maestro
                    ->get_cache_master()
                    ->setup_caching()
                ;
            }
        );

