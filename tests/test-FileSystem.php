<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\AbstractFileSystem;

/**
 * @group AbstractFileSystem
 */
class test_AbstractFileSystem extends vendi_cache_test_base
{
    /**
     * @covers \Vendi\Cache\AbstractFileSystem::__construct
     * @covers \Vendi\Cache\AbstractFileSystem::get_maestro
     */
    public function test_concrete_methods()
    {
        $stub = $this->getMockForAbstractClass('\Vendi\Cache\AbstractFileSystem', [$this->__get_new_maestro(), '']);
        $this->assertInstanceOf('\Vendi\Cache\Maestro', $stub->get_maestro());
    }
}
