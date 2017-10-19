<?php

namespace Vendi\Cache\CacheBypasses;

use Assert\Assertion;
use Vendi\Cache\Maestro;

abstract class AbstractCacheBypassWithConstantAndFunction extends AbstractCacheBypass
{
    private $_constant;

    abstract public function test_specific_function_and_log_failure();

    public function __construct(Maestro $maestro, $constant)
    {
        //Sanity check params
        Assertion::notEmpty($constant);
        Assertion::string($constant);

        parent::__construct($maestro);
        $this->_constant = $constant;
    }

    public function get_constant()
    {
        return $this->_constant;
    }

    final public function is_cacheable()
    {
        if (!$this->test_constant()) {
            return false;
        }

        //This function is defined in wp-includes/load.php and is guaranteed to
        //exist as long as WP exists.
        if (!$this->test_specific_function_and_log_failure()) {
            return false;
        }

        return true;
    }

    final public function test_constant()
    {
        $name = $this->_constant;

        $settings = $this->get_secretary();

        //We're looking for hard-stop constants. If the constant doesn't exist
        //then assume that we can cache this resource.
        if (! $settings->is_constant_defined($name)) {
            return true;
        }

        //Constants are assumed to be boolean
        $result = $settings->get_constant_value($name);
        $result = (bool) $settings->get_constant_value($name);

        //The constant _IS_ defined but set to a false-like value. Super weird
        //and I'm pretty sure this should never happen but still technically
        //valid as far as PHP is concerned.
        if (false === $result) {
            $this
                ->get_maestro()
                ->get_logger()
                ->debug(
                            'Strange state - constant set to false',
                            [
                                'constant' => $name,
                            ]
                    );
            return true;
        }

        $this->log_request_as_not_cacheable(
                                                [
                                                    'reason' => sprintf(__('The constant %1$s is set and true', 'vendi-cache'), $this->_constant),
                                                    'extra'  => "Constant $name is false",
                                                ]
                                        );

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
