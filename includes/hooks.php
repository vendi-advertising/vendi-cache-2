<?php

add_action(
            'plugins_loaded',
            function()
            {
                \Vendi\Cache\Master::get_default_instance()
                    ->get_cache_master()
                    ->setup_caching()
                ;
            }
        );
