<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\Tests\vendi_cache_test_base;

class cache_bypass_base extends vendi_cache_test_base
{
    public function _test_is_cacheable_because_fatal_constant_not_defined( $class_to_test, $name )
    {
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_secretary();

        //The supplied constant should not exist by default
        $this->assertFalse( $cache_settings->is_constant_defined( $name ) );

        //If the constant doesn't exist then we assume the resource is cacheable
        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $this->assertFalse( $test->is_resource_not_cacheable_because_constant_is_true() );

        $cache_settings->set_constant( $name, true );

        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $this->assertTrue( $test->is_resource_not_cacheable_because_constant_is_true() );

        //Constant is define but weirdly set to false. This me
        $cache_settings->set_constant( $name, false );

        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj( $maestro );
        $this->assertFalse( $test->is_resource_not_cacheable_because_constant_is_true() );
    }
}
