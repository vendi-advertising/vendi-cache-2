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

                $maestro
                    ->get_cache_master()
                    ->setup_caching()
                ;
            }
        );
