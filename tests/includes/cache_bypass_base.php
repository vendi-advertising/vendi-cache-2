<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

class cache_bypass_base extends vendi_cache_test_base_no_wordpress
{
    public function _test_is_cacheable_because_fatal_constant_not_defined($class_to_test, $name)
    {
        $logger = [];
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_secretary();

        //The supplied constant should not exist by default
        $this->assertFalse($cache_settings->is_constant_defined($name));

        //If the constant doesn't exist then we assume the resource is cacheable
        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj($maestro);
        $this->assertFalse($test->is_resource_not_cacheable_because_constant_is_true());
        $this->assertFalse($test->is_resource_not_cacheable());
        $cache_settings->set_constant($name, true);

        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj($maestro);
        $this->assertTrue($test->is_resource_not_cacheable_because_constant_is_true());
        $this->assertTrue($test->is_resource_not_cacheable());

        $this->assertSameLastMessage('Request not cacheable');

        //Constant is define but weirdly set to false.
        $cache_settings->set_constant($name, false);

        $obj = "\\Vendi\\Cache\\CacheBypasses\\$class_to_test";
        $test = new $obj($maestro);
        $this->assertFalse($test->is_resource_not_cacheable_because_constant_is_true());
        $this->assertSameLastMessage('Strange state - constant set to false');
    }
}
