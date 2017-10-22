<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstantAndFunction;
use Vendi\Cache\Maestro;
use Vendi\Cache\Tests\vendi_cache_test_base;

class test_2 extends AbstractCacheBypassWithConstantAndFunction
{
    private $_value_to_return;

    public function __construct(Maestro $maestro, $constant, $value_to_return = null)
    {
        parent::__construct($maestro, $constant);
        $this->_value_to_return = $value_to_return;
    }

    public function is_resource_not_cacheable_because_function_says_so()
    {
        return $this->_value_to_return;
    }
}

class test_AbstractCacheBypassWithConstantAndFunction extends vendi_cache_test_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstantAndFunction::__construct
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstantAndFunction::is_resource_not_cacheable
     * @covers \Vendi\Cache\CacheBypasses\AbstractCacheBypassWithConstantAndFunction::is_resource_not_cacheable_because_function_says_so
     * @dataProvider provider_for_test___all
     * @param mixed $constant_value
     * @param mixed $function_return_value
     * @param mixed $is_resource_not_cacheable
     */
    public function test___all($constant_value, $function_return_value, $is_resource_not_cacheable)
    {
        $mock = new test_2($this->__get_new_maestro(), 'TEST_DO_NOT_CACHE_CHEESE', $function_return_value);
        $this->assertFalse($mock->get_secretary()->is_constant_defined('TEST_DO_NOT_CACHE_CHEESE'));
        $mock->get_secretary()->set_constant('TEST_DO_NOT_CACHE_CHEESE', $constant_value);

        $this->assertSame($constant_value, $mock->is_resource_not_cacheable_because_constant_is_true());
        $this->assertSame($function_return_value, $mock->is_resource_not_cacheable_because_function_says_so());
        $this->assertSame($is_resource_not_cacheable, $mock->is_resource_not_cacheable());
    }

    public function provider_for_test___all()
    {
        return [
                    // $constant_value, $function_return_value,     $is_resource_not_cacheable

                    //Constant set to true, function returns true,  resource is NOT cacheable
                    [  true,                true,                   true ],

                    //Constant set to true, function returns false, resource is NOT cacheable
                    [  true,                true,                   true ],

                    //Constant set to false(weird), function returns true, resource is NOT cacheable
                    [  false,               true,                   true ],

                    //Constant set to false(weird), function returns false, resource is cacheable
                    [  false,               false,                  false ],
        ];
    }
}
