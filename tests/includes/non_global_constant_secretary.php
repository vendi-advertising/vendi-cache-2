<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\Secretary;

final class non_global_constant_secretary extends Secretary
{
    private $_CONSTANTS = [];

    private $_FUNCTIONS = [];

    public function reset_all()
    {
        $this->_CONSTANTS = [];
        $this->_FUNCTIONS = [];
    }

    public function set_constant($name, $value)
    {
        $this->_CONSTANTS[ $name ] = $value;
    }

    public function unset_constant($name)
    {
        unset($this->_CONSTANTS[ $name ]);
    }

    public function is_constant_defined($name)
    {
        return \array_key_exists($name, $this->_CONSTANTS);
    }

    public function get_constant_value($name)
    {
        if (! $this->is_constant_defined($name)) {
            throw new \Exception(\sprintf(__('Attempt at using constant %1$s before checking for definition', 'vendi-cache'), $name));
        }
        return $this->_CONSTANTS[ $name ];
    }

    public function set_function($name, callable $callback)
    {
        $this->_FUNCTIONS[ $name ] = $callback;
    }

    public function does_function_exist($name)
    {
        return \array_key_exists($name, $this->_FUNCTIONS);
    }

    public function invoke_function($name, array $args = [])
    {
        if (!$this->does_function_exist($name)) {
            throw new \Exception(\sprintf(__('Attempt at invoking function %1$s before checking for definition', 'vendi-cache'), $name));
        }
        return \call_user_func_array($this->_FUNCTIONS[$name], $args);
    }
}
