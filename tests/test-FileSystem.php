<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\FileSystem;

/**
 * @group FileSystem
 */
class test_FileSystem extends vendi_cache_test_base
{
    /**
     * @covers \Vendi\Cache\FileSystem::__construct
     * @covers \Vendi\Cache\FileSystem::get_maestro
     */
    public function test_concrete_methods()
    {
        $stub = $this->getMockForAbstractClass('\Vendi\Cache\FileSystem', [$this->__get_new_maestro()]);
        $this->assertInstanceOf('\Vendi\Cache\Maestro', $stub->get_maestro());
    }
}
