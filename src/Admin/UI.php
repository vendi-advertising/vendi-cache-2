<?php declare(strict_types=1);
namespace Vendi\Cache\Admin;

use Vendi\Cache\AbstractMaestroEnabledBase;

class UI extends AbstractMaestroEnabledBase
{
    const URL_SLUG = 'vendi-cache-2-settings';

    public function get_current_tab()
    {
        $params = $this
                ->get_maestro()
                ->get_request()
                ->getQueryParams()
            ;
        if (\array_key_exists('tab', $params)) {
            return $params[ 'tab' ];
        }

        return '';
    }

    public function get_tab_url($tab)
    {
        return add_query_arg(
                                [
                                    'page' => self::URL_SLUG,
                                    'tab'  => $tab,

                                ],
                                admin_url('options-general.php')
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

        foreach ($all_tabs as $tab_key => $tab_name) {
            $selected = '';

            if ($current_tab === $tab_key) {
                $selected = ' class="selected"';
            }

            $ret .= \sprintf(
                                '<li%3$s><a href="%1$s">%2$s</a></li>',
                                esc_url($this->get_tab_url($tab_key)),
                                esc_html($tab_name),
                                $selected
                        );
        }

        $ret .= '</ul>';
        return $ret;
    }

    public function handle_post($current_tab)
    {
        check_admin_referer("vendi-cache-$current_tab");
    }

    public function handle_page_routing($echo = true)
    {
        $current_tab = $this->get_current_tab();

        $all_tabs = $this->get_all_tabs_associative();

        if (! \array_key_exists($current_tab, $all_tabs)) {
            $keys = \array_keys($all_tabs);
            $current_tab = \reset($keys);
        }

        if ('POST' === $this->get_maestro()->get_request()->getMethod()) {
            $this->handle_post($current_tab);
        }

        $ret = $this->get_html_for_tab($current_tab);
        if ($echo) {
            echo $ret;
        }

        return $ret;
    }

    public function get_html_for_tab($current_tab)
    {
        $ret = '';

        $template_options = [];

        switch ($current_tab) {
            case 'cache-mode':
                $template_options = [
                                        $this->get_maestro()->get_secretary()->get_named_option('CacheMode'),
                                    ];
                break;

            case 'cache-options':
                $template_options = [
                                        $this->get_maestro()->get_secretary()->get_named_option('DebugComment'),
                                        $this->get_maestro()->get_secretary()->get_named_option('DebugLogging'),
                                    ];
                break;

            default:
                $template_options = [ ];
                break;
        }

        $ret .= '<div class="wrap">';
        $ret .= \sprintf(
                        '<h1>%1$s</h1>',
                        esc_html(__('Vendi Cache Settings', 'vendi-cache'))
                );

        $ret .= $this->get_tabs();

        $ret .= '<div class="vendi-cache-2-admin-wrap">';
        $ret .= '<form method="post">';

        $ret .= wp_nonce_field("vendi-cache-$current_tab", '_wpnonce', true, false);

        if (\count($template_options) > 0) {
            $ret .= '<div class="fields outer-box">';

            foreach ($template_options as $template_option) {
                $ret .= '<p>';
                $ret .= $template_option->get_html();
                $ret .= '</p>';
            }

            $ret .= '<p><br /><br /><input type="submit" value="Submit" /></p>';
            $ret .= '</div>';
        }

        \ob_start();
        require VENDI_CACHE_DIR . "/templates/$current_tab.php";
        $ret .= \ob_get_clean();

        $ret .= '</form>';
        $ret .= '</div>';

        $ret .= '</div>';

        return $ret;
    }
}
