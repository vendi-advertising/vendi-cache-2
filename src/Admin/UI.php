<?php

namespace Vendi\Cache\Admin;

use Vendi\Cache\Maestro;

class UI
{

    const URL_SLUG = 'vendi-cache-2-settings';

    private static $_maestro;

    private function __construct( Maestro $maestro )
    {
        $this->_maestro = $maestro;
    }

    /**
     * [get_maestro description]
     * @return Maestro
     */
    public function get_maestro()
    {
        return $this->_maestro;
    }

    public function handle_page_routing()
    {
        require VENDI_CACHE_DIR . '/templates/cache-settings.php';
    }
}
