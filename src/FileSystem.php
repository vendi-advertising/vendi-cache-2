<?php declare(strict_types=1);
namespace Vendi\Cache;

use Assert\Assertion;
use Webmozart\PathUtil\Path;

class FileSystem extends AbstractMaestroEnabledBase
{
    private $_last_error;

    private $_root;

    public function __construct(Maestro $maestro)
    {
        parent::__construct($maestro);
        $this->_root = $maestro->get_secretary()->get_cache_folder_abs();
    }

    final public function get_root()
    {
        return $this->_root;
    }

    final public function set_root($root)
    {
        $this->_root = $root;
    }

    final public function get_last_error()
    {
        return $this->_last_error;
    }

    public function handle_error($errno, $errstr, $errfile, $errline)
    {
        $this->_last_error = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public function file_exists($relative_path)
    {
        return $this->file_exists_abs(Path::join($this->get_root(), $relative_path));
    }

    public function delete_file($relative_path)
    {
        return $this->delete_file_abs(Path::join($this->get_root(), $relative_path));
    }

    public function file_exists_abs($abs_path)
    {
        return \is_file($abs_path);
    }

    public function perform_trapped_function(callable $func, array $args = [], $error_types = \E_WARNING)
    {
        \set_error_handler([$this, 'handle_error'], $error_types);
        $result = @$func($args);
        \restore_error_handler();
    }

    public function delete_file_abs($abs_path)
    {
        //The file doesn't exist which is the outcome that we're actually looking
        //for so we consider this a success.
        if (!$this->file_exists_abs($abs_path)) {
            $this->get_maestro()->get_logger()->debug(
                                                        __('Delete file request', 'vendi-cache'),
                                                        [
                                                            'file' => $abs_path,
                                                            'status' => __('File Not Found', 'vendi-cache'),
                                                        ]
                                                    );
            return true;
        }

        //We are very deep in the backend and permissions can get a little weird.
        //Also, rouge system admins like to delete things willy-nilly. So we're
        //going to trap the unlink function.
        $this->perform_trapped_function([$this,'_do_delete_file_abs'], [$abs_path]);

        if ($this->get_last_error()) {
            $this->get_maestro()->get_logger()->error(
                                                        __('Delete file request', 'vendi-cache'),
                                                        [
                                                            'file' => $abs_path,
                                                            'status' => __('Error', 'vendi-cache'),
                                                            'error' => $this->get_last_error(),
                                                        ]
                                                    );
            return false;
        }

        $this->get_maestro()->get_logger()->debug(
                                                    __('Delete file request', 'vendi-cache'),
                                                    [
                                                        'file' => $abs_path,
                                                        'status' => __('Success', 'vendi-cache'),
                                                    ]
                                                );

        return true;
    }

    public function _do_delete_file_abs(array $params)
    {
        Assertion::count($params, 1);
        $abs_path = \array_shift($params);

        if (!\unlink($abs_path)) {
            $this->_last_error = new \Exception('unlink() on file failed');
        }
    }

    public function delete_dir($relative_path)
    {
        return $this->delete_dir_abs(Path::join($this->get_root(), $relative_path));
    }

    public function delete_dir_abs($abs_path)
    {
        //If we don't have an actual folder, skip it
        if (! \is_dir($abs_path)) {
            $this->get_maestro()->get_logger()->debug(
                                                        __('Delete directory request', 'vendi-cache'),
                                                        [
                                                            'directory' => $abs_path,
                                                            'status' => __('Directory not found', 'vendi-cache'),
                                                        ]
                                                    );
            return true;
        }

        $this->perform_trapped_function([$this, '_do_delete_dir_abs'], [$abs_path]);

        if ($this->get_last_error()) {
            $this->get_maestro()->get_logger()->error(
                                                        __('Delete directory request', 'vendi-cache'),
                                                        [
                                                            'directory' => $abs_path,
                                                            'status' => __('Error', 'vendi-cache'),
                                                            'error' => $this->get_last_error(),
                                                        ]
                                                    );
            return false;
        }

        $this->perform_trapped_function([$this, '_do_delete_dir_abs__root_dir'], [$abs_path]);

        if ($this->get_last_error()) {
            $this->get_maestro()->get_logger()->error(
                                                        __('Delete directory request', 'vendi-cache'),
                                                        [
                                                            'directory' => $abs_path,
                                                            'status' => __('Error', 'vendi-cache'),
                                                            'error' => $this->get_last_error(),
                                                        ]
                                                    );
            return false;
        }

        return true;
    }

    public function _do_delete_dir_abs__root_dir(array $params)
    {
        Assertion::count($params, 1);
        $abs_path = \array_shift($params);

        \rmdir($abs_path);
    }

    public function _do_delete_dir_abs(array $params)
    {
        Assertion::count($params, 1);
        $abs_path = \array_shift($params);

        //This actually might error if we don't have enough permissions to even
        //see inside of the directory.
        $files = $this->get_directory_contents_abs($abs_path);

        if (!$files) {
            return;
        }

        try {
            // Delete all children.
            foreach ($files as $fileinfo) {
                $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                if (!$action($fileinfo->getPathname())) {
                    $this->_last_error = new \Exception("$action() on object failed");
                    return; // Abort due to the failure.
                }
            }
        } catch (\Exception $e) {
            $this->_last_error = $e;
        }
    }

    public function write_file($relative_path, $contents)
    {
        return $this->write_file_abs(Path::join($this->get_root(), $relative_path), $contents);
    }

    public function _do_mkdir_abs(array $params)
    {
        Assertion::count($params, 1);
        $abs_path = \array_shift($params);
        $result = \mkdir($abs_path);
        if (!$result || !\is_dir($abs_path)) {
            $this->_last_error = new \Exception("mkdir() on $abs_path failed");
        }
    }

    public function _do_write_file_abs(array $params)
    {
        Assertion::count($params, 2);
        $abs_path = \array_shift($params);
        $contents = \array_shift($params);
        \file_put_contents($abs_path, $contents);
    }

    public function write_file_abs($abs_path, $contents)
    {
        $dir = \dirname($abs_path);
        if (!\is_dir($dir)) {
            $this->perform_trapped_function([$this,'_do_mkdir_abs'], [$dir]);
            if ($this->get_last_error()) {
                $this->get_maestro()->get_logger()->error(
                                                            __('Create directory request', 'vendi-cache'),
                                                            [
                                                                'path' => $dir,
                                                                'status' => __('Error', 'vendi-cache'),
                                                                'error' => $this->get_last_error(),
                                                            ]
                                                        );
                return false;
            }
        }

        $this->perform_trapped_function([$this,'_do_write_file_abs'], [$abs_path, $contents]);
        if ($this->get_last_error()) {
            $this->get_maestro()->get_logger()->error(
                                                        __('Write file request', 'vendi-cache'),
                                                        [
                                                            'path' => $abs_path,
                                                            'status' => __('Error', 'vendi-cache'),
                                                            'error' => $this->get_last_error(),
                                                        ]
                                                    );
            return false;
        }

        return true;
    }

    public function get_directory_contents($relative_path)
    {
        return $this->get_directory_contents_abs(Path::join($this->get_root(), $relative_path));
    }

    public function get_directory_contents_abs($abs_path)
    {
        try {
            return new \RecursiveIteratorIterator(
                                                        new \RecursiveDirectoryIterator(
                                                                                            $abs_path,
                                                                                            \RecursiveDirectoryIterator::SKIP_DOTS
                                                                                    ),
                                                        \RecursiveIteratorIterator::CHILD_FIRST
            );
        } catch (\Exception $e) {
            $this->_last_error = $e;
            return null;
        }
    }
}
