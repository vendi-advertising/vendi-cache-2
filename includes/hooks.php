<?php

add_action(
            'plugins_loaded',
            function()
            {
                \Vendi\Cache\CacheMaster::get_instance()->setup_caching();
            }
        );
