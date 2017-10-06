<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\Tests\cache_bypass_base;
use Vendi\Cache\CacheBypasses\LegacyConstants;

class test_LegacyConstants extends cache_bypass_base
{

    /**
     * @covers Vendi\Cache\CacheBypasses\LegacyConstants::is_cacheable
     * @dataProvider provider_for_test_is_cacheable
     */
    public function test_is_cacheable( $name )
    {
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_cache_settings();
        $this->assertFalse( $cache_settings->is_constant_defined( $name ) );

        $test = new LegacyConstants( $maestro );
        $this->assertTrue( $test->is_cacheable() );

        $cache_settings->set_constant( $name, true );
        $this->assertFalse( $test->is_cacheable() );
    }

    public function provider_for_test_is_cacheable()
    {
        $legacy_cache_constants = [
                                    [ 'WFDONOTCACHE' ],
                                    [ 'DONOTCACHEPAGE' ],
                                    [ 'DONOTCACHEDB' ],
                                    [ 'DONOTCACHEOBJECT' ],
                                 ];

        return $legacy_cache_constants;
    }

}
