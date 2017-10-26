<?php declare(strict_types=1);
namespace Vendi\Cache;

abstract class AbstractFileSystem
{
    private $_maestro;

    public function __construct(Maestro $maestro)
    {
        $this->_maestro = $maestro;
    }

    final public function get_maestro()
    {
        return $this->_maestro;
    }

    abstract public function file_exists($file_path);

    abstract public function delete_file($file_path);

    abstract public function delete_dir($dir_path, array $except_files = []);

    abstract public function write_file($file_path, $contents);

    abstract public function get_directory_contents($file_path);
}
