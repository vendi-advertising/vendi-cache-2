<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\FileSystem;
use Vendi\Cache\Maestro;

/**
 * @coversNothing
 */
class file_system_for_tests extends FileSystem
{
    public function __construct(Maestro $maestro, $root)
    {
        parent::__construct($maestro);
        $this->set_root($root);
    }
}
