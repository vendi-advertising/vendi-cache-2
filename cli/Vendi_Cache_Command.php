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
        \Vendi\Cache\CacheMaster::get_instance()->clear_entire_page_cache();
    }
}
