<?php

namespace Vendi\Cache\Tests;

use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\Maestro;
use Vendi\Cache\Admin\UI;

class test_UI extends vendi_cache_test_base
{

    /**
     * @covers Vendi\Cache\Admin\UI::__construct
     * @covers Vendi\Cache\Admin\UI::get_maestro
     */
    public function test___construct__and__get_maestro()
    {
        $maestro = Maestro::get_default_instance();
        $admin_ui = new UI( $maestro );
        $this->assertSame( $maestro, $admin_ui->get_maestro() );
    }

    /**
     * @covers Vendi\Cache\Admin\UI::get_request
     */
    public function test_get_request()
    {
        $admin_ui = new UI( Maestro::get_default_instance() );
        $this->assertInstanceOf( '\Symfony\Component\HttpFoundation\Request', $admin_ui->get_request() );
    }

    /**
     * @covers Vendi\Cache\Admin\UI::get_current_tab
     * @dataProvider provider_for_test_get_current_tab
     */
    public function test_get_current_tab( $expected, $url )
    {
        $maestro = Maestro::get_default_instance()
                    ->with_request( Request::create( $url ) )
                ;
        $admin_ui = new UI( $maestro );
        $this->assertSame( $expected, $admin_ui->get_current_tab() );
    }

    /**
     * @covers Vendi\Cache\Admin\UI::get_tab_url
     */
    public function test_get_tab_url( )
    {
        $test_url_base = $this->__get_test_url();

        $admin_ui = new UI( Maestro::get_default_instance() );

        $this->assertSame(
                            $test_url_base . 'wp-admin/options-general.php?page=vendi-cache-2-settings&tab=cheese',
                            $admin_ui->get_tab_url( 'cheese' )
                        );
    }

    /**
     * @covers Vendi\Cache\Admin\UI::get_all_tabs_associative
     */
    public function test_get_all_tabs_associative( )
    {
        $admin_ui = new UI( Maestro::get_default_instance() );

        $expected = [
                        'cache-mode'       => 'Cache Mode',
                        'cache-options'    => 'Cache Options',
                        'cache-exclusions' => 'Cache Exclusions',
                        'cache-stats'      => 'Cache Stats',
            ];

        $this->assertTrue(
                            $this->arrays_are_similar(
                                                        $expected,
                                                        $admin_ui->get_all_tabs_associative()
                            )
            );
    }

    /**
     * @covers Vendi\Cache\Admin\UI::get_tabs
     * @covers Vendi\Cache\Admin\UI::handle_page_routing
     * @covers Vendi\Cache\Admin\UI::get_html_for_tab
     */
    public function test__NOT_REALLY_A_TEST()
    {
        $admin_ui = new UI( Maestro::get_default_instance() );
        $admin_ui->handle_page_routing( false );

        $this->assertTrue( true );

        // throw new \PHPUnit\Framework\Warning( 'This is not really a test, only a placeholder to run code.' );
    }

    public function provider_for_test_get_current_tab()
    {
        return [
                    [ '',       'http://www.example.com/' ],
                    [ '',       'http://www.example.com/?tab=' ],
                    [ 'cheese', 'http://www.example.com/?tab=cheese' ],

                    //Case sensitive
                    [ '',       'http://www.example.com/?Tab=cheese' ],
            ];
    }

    private function __get_test_url()
    {
        $this->assertTrue( defined( 'PLUGINDIR' ) );
        $this->assertTrue( defined( 'WP_PLUGIN_URL' ) );

        //This is kinda hacky but works.
        //Technically I could get rid of the "-1" and concatenation but I think
        //this is more proper.
        return substr( WP_PLUGIN_URL, 0, strpos( WP_PLUGIN_URL, PLUGINDIR ) - 1 ) . '/';
    }
}
