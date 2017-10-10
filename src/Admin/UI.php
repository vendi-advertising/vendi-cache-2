<?php

namespace Vendi\Cache\Admin;

use Assert\Assertion;
use Vendi\Cache\Maestro;

class UI
{

    const URL_SLUG = 'vendi-cache-2-settings';

    private static $_maestro;

    public function __construct( Maestro $maestro )
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

    public function get_request()
    {
        $request = $this->get_maestro()->get_request();
        Assertion::isInstanceOf( $request, 'Symfony\Component\HttpFoundation\Request' );
        return $request;
    }

    public function handle_page_routing()
    {
        $request = $this->get_request();

        $tab = $request->query->get( 'tab' );

        switch( $tab )
        {
            case 'cache-mode':
            case 'cache-options':
            case 'cache-exclusions':
            case 'cache-stats':
                break;

            default:
                $tab = 'cache-mode';
        }

        global $template_maestro;

        Assertion::null( $template_maestro );
        $template_maestro = $this->get_maestro();
        require VENDI_CACHE_DIR . "/templates/$tab.php";
        $template_maestro = null;
    }
}
