<?php

declare(strict_types=1);

namespace Vendi\Cache\Tests;

use Vendi\Cache\ErrorHandler;

class test_ErrorHandler extends vendi_cache_test_base_no_wordpress
{
    /**
     * @covers \Vendi\Cache\ErrorHandler::handle_exception
     */
    public function test__handle_exception()
    {
        $maestro = $this->__get_new_maestro();
        $secretary = $maestro->get_secretary();

        $eh = new ErrorHandler($maestro);

        //This is the magic global function, check to see if it is invoked
        $is_called = false;
        global $vendi_cache_old_exception_handler;
        $vendi_cache_old_exception_handler = function () use (&$is_called) {
            $is_called = true;
        };

        //The constant shouldn't be set yet
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_PHP_ERROR'));

        //Trigger the handler
        $eh->handle_exception(new \Exception('Test'));

        //It should be defined and our callback should have been called
        $this->assertTrue($secretary->is_constant_defined('VENDI_CACHE_PHP_ERROR'));
        $this->assertTrue($is_called);
    }

    /**
     * @covers \Vendi\Cache\ErrorHandler::handle_error
     */
    public function test__handle_error()
    {
        $maestro = $this->__get_new_maestro();
        $secretary = $maestro->get_secretary();

        $eh = new ErrorHandler($maestro);

        //The constant shouldn't be set yet
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_PHP_ERROR'));

        //Trigger the handler
        $this->assertFalse($eh->handle_error(1, ''));

        //It should be defined and our callback should have been called
        $this->assertTrue($secretary->is_constant_defined('VENDI_CACHE_PHP_ERROR'));
    }
}
