<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\CacheBypasses\LegacyConstants;
use Vendi\Cache\Tests\cache_bypass_base;

class test_LegacyConstants extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\LegacyConstants::is_resource_not_cacheable
     * @dataProvider provider_for_test_is_resource_not_cacheable
     * @param mixed $name
     */
    public function test_is_resource_not_cacheable($name)
    {
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_secretary();
        $this->assertFalse($cache_settings->is_constant_defined($name));

        $test = new LegacyConstants($maestro);
        $this->assertFalse($test->is_resource_not_cacheable());

        $cache_settings->set_constant($name, true);
        $this->assertTrue($test->is_resource_not_cacheable());
    }

    public function provider_for_test_is_resource_not_cacheable()
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
