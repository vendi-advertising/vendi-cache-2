<?php declare(strict_types=1);
namespace Vendi\Cache;

use Webmozart\PathUtil\Path;

final class FileSystem extends AbstractFileSystem
{
    private $_root;

    private $_last_error;

    public function __construct(Maestro $maestro)
    {
        parent::__construct($maestro);
        $this->_root = $maestro->get_secretary()->get_cache_folder_abs();
    }

    public function handle_error($errno, $errstr, $errfile, $errline)
    {
        $this->_last_error = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public function file_exists($relative_path)
    {
        return $this->file_exists(Path::join($this->_root, $relative_path));
    }

    public function delete_file($relative_path)
    {
        return $this->delete_file_abs(Path::join($this->_root, $relative_path));
    }

    public function file_exists_abs($abs_path)
    {
        return is_file($abs_path);
    }

    public function perform_trapped_function(callable $func, $error_types = \E_WARNING)
    {
        set_error_handler([$this, 'handle_error'], $error_types);
        $result = @$func();
        restore_error_handler();
        return $result;
    }

    public function delete_file_abs($abs_path)
    {
        //The file doesn't exist which is the outcome that we're actually looking
        //for so we consider this a success.
        if (!$this->file_exists($abs_path)) {
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
        $result = $this->perform_trapped_function(
                                                    function () use ($abs_path) {
                                                        return unlink($abs_file);
                                                    }
                                                );

        if ($this->_last_error) {
            $this->get_maestro()->get_logger()->error(
                                                        __('Delete file request', 'vendi-cache'),
                                                        [
                                                            'file' => $abs_path,
                                                            'status' => __('Error', 'vendi-cache'),
                                                            'error' => $this->_last_error,
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

        return $result;
    }

    public function delete_dir($relative_path, array $except_files = [])
    {
        return $this->delete_dir(Path::join($this->_root, $relative_path));
    }

    public function delete_dir_abs($abs_path, array $except_files = [])
    {
        //If we don't have an actual folder, skip it
        if (! is_dir($absolute_path)) {
            $this->get_maestro()->get_logger()->debug(
                                                        __('Delete directory request', 'vendi-cache'),
                                                        [
                                                            'directory' => $abs_path,
                                                            'status' => __('Directory not found', 'vendi-cache'),
                                                        ]
                                                    );
            return true;
        }

        $result = $this->perform_trapped_function(
                                                    function () {
                                                        // Delete all children.
                                                        $files = new \RecursiveIteratorIterator(
                                                                                                    new \RecursiveDirectoryIterator(
                                                                                                                                        $abs_path,
                                                                                                                                        \RecursiveDirectoryIterator::SKIP_DOTS
                                                                                                                                ),
                                                                                                    \RecursiveIteratorIterator::CHILD_FIRST
                                                        );
                                                        foreach ($files as $fileinfo) {
                                                            $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                                                            if (!$action($fileinfo->getRealPath())) {
                                                                return false; // Abort due to the failure.
                                                            }
                                                        }
                                                        return true;
                                                    }
        );

        if ($this->_last_error) {
            $this->get_maestro()->get_logger()->error(
                                                        __('Delete directory request', 'vendi-cache'),
                                                        [
                                                            'directory' => $abs_path,
                                                            'status' => __('Error', 'vendi-cache'),
                                                            'error' => $this->_last_error,
                                                        ]
                                                    );
            return false;
        }

        $result = $this->perform_trapped_function(
                                                    function () use ($abs_path) {
                                                        return rmdir($abs_path);
                                                    }
        );

        if ($this->_last_error) {
            $this->get_maestro()->get_logger()->error(
                                                        __('Delete directory request', 'vendi-cache'),
                                                        [
                                                            'directory' => $abs_path,
                                                            'status' => __('Error', 'vendi-cache'),
                                                            'error' => $this->_last_error,
                                                        ]
                                                    );
            return false;
        }

        return $result;
    }

    public function write_file($relative_path, $contents)
    {
        return $this->get_league_fs()->write($relative_path, $contents);
    }

    public function write_file_abs($abs_path, $contents)
    {
        file_put_contents($abs_path, $contents);
    }

    public function get_directory_contents($relative_path)
    {
        return $this->get_directory_contents_abs(Path::join($this->_root, $relative_path));
    }

    public function get_directory_contents_abs($abs_path)
    {
        return new \RecursiveIteratorIterator(
                                                    new \RecursiveDirectoryIterator(
                                                                                        $abs_path,
                                                                                        \RecursiveDirectoryIterator::SKIP_DOTS
                                                                                ),
                                                    \RecursiveIteratorIterator::CHILD_FIRST
        );
    }
}
