<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

class cache_bypass_base extends vendi_cache_test_base
{
    public $_logs = [];

    public function assertSameLastMessage($expected, $do_not_purge_logs = false)
    {
        if (0===count($this->_logs)) {
            throw new \Exception('No last message received');
        }

        $last_message = end($this->_logs);

        if (!$do_not_purge_logs) {
            $this->_purge_logs();
        }

        $this->assertArrayHasKey('message', $last_message);

        $this->assertSame($expected, $last_message['message']);
    }

    public function _purge_logs()
    {
        $this->_logs = [];
    }

    public function _handle_logger(array $record)
    {
        $this->_logs[] = $record;
    }

    public function _test_is_cacheable_because_fatal_constant_not_defined($class_to_test, $name)
    {
        $logger = [];
        $maestro = $this->__get_new_maestro(
                                                null,
                                                [$this, '_handle_logger']
                                        );
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
