<?php

namespace Vendi\Cache\Admin;

use Assert\Assertion;
use Vendi\Cache\Maestro;

class UI
{

    const URL_SLUG = 'vendi-cache-2-settings';

    private $_maestro;

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

    public function get_current_tab()
    {
        return $this
                ->get_request()
                ->query
                ->get( 'tab' )
            ;
    }

    public function get_tab_url( $tab )
    {
        return add_query_arg(
                                [
                                    'page' => self::URL_SLUG,
                                    'tab'  => $tab,

                                ],
                                admin_url( 'options-general.php' )
                            );
    }

    public function get_all_tabs_associative()
    {
        return [
                    'cache-mode'       => 'Cache Mode',
                    'cache-options'    => 'Cache Options',
                    'cache-exclusions' => 'Cache Exclusions',
                    'cache-stats'      => 'Cache Stats',
            ];
    }

    public function get_tabs()
    {
        $current_tab = $this->get_current_tab();

        $all_tabs = $this->get_all_tabs_associative();

        $ret = '<ul class="vendi-cache-2-admin-tabs">';

        foreach( $all_tabs as $tab_key => $tab_name )
        {
            $selected = '';

            if( $current_tab === $tab_key )
            {
                $selected = ' class="selected"';
            }

            $ret .= sprintf(
                                '<li%3$s><a href="%1$s">%2$s</a></li>',
                                esc_url( $this->get_tab_url( $tab_key ) ),
                                esc_html( $tab_name ),
                                $selected
                        );
        }

        $ret .= '</ul>';
        return $ret;
    }

    public function handle_page_routing()
    {
        $current_tab = $this->get_current_tab();

        $all_tabs = $this->get_all_tabs_associative();

        if( ! array_key_exists( $current_tab, $all_tabs ) )
        {
            $keys = array_keys( $all_tabs );
            $current_tab = reset( $keys );
        }

        //Temporarily store our local Maestro in a global variable
        global $template_maestro;
        Assertion::null( $template_maestro );
        $template_maestro = $this->get_maestro();

        echo '<div class="wrap">';
        echo sprintf(
                        '<h1>%1$s</h1>',
                        esc_html( __( 'Vendi Cache Settings', 'vendi-cache' ) )
                );

        echo $this->get_tabs();

        echo '<div class="vendi-cache-2-admin-wrap">';
        require VENDI_CACHE_DIR . "/templates/$current_tab.php";
        echo '</div>';

        //Reset our global
        $template_maestro = null;

        echo '</div>';
    }
}
