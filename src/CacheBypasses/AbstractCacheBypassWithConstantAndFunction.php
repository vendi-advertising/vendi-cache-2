<?php declare(strict_types=1);
namespace Vendi\Cache\CacheBypasses;

abstract class AbstractCacheBypassWithConstantAndFunction extends AbstractCacheBypassWithConstant
{
    private $_constant;

    abstract public function is_resource_not_cacheable_because_function_says_so();

    final public function is_resource_not_cacheable()
    {
        if ($this->is_resource_not_cacheable_because_constant_is_true()) {
            return true;
        }

        if ($this->is_resource_not_cacheable_because_function_says_so()) {
            return true;
        }

        return false;
    }

    final public function log_request_as_not_cacheable_because_function_returned_value($name, $value)
    {
        $this->log_request_as_not_cacheable(
                                                [
                                                    'reason' => "Required function $name return $value",
                                                ]
            );
    }
}
