<?php declare(strict_types=1);
namespace Vendi\Cache;

abstract class AbstractFileSystem
{
    private $_root;

    private $_maestro;

    public function __construct(Maestro $maestro, $root)
    {
        $this->_maestro = $maestro;
        $this->_root = $root;
    }

    final public function get_maestro()
    {
        return $this->_maestro;
    }

    final public function get_root()
    {
        return $this->_root;
    }

    final public function set_root($root)
    {
        $this->_root = $root;
    }

    abstract public function file_exists($relative_path);

    abstract public function delete_file($relative_path);

    abstract public function delete_dir($dir_path, array $except_files = []);

    abstract public function write_file($relative_path, $contents);

    abstract public function get_directory_contents($relative_path);
}
