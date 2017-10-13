<?php

/**
 * Manipulates Vendi Cache.
 *
 * TODO: Write description.
 */
class Vendi_Cache_Command extends \WP_CLI_Command
{

    /**
     * Flushes the cache.
     */
    public function flush()
    {
        //Get our default master
        $maestro = \Vendi\Cache\Maestro::get_default_instance();

        //Log the start of bootup
        $maestro
            ->get_logger()
            ->debug( 'Plugin loading - Invoked via WP-CLI' )
        ;

        $maestro
            ->get_cache_master()
            ->clear_entire_page_cache()
        ;
    }
}
