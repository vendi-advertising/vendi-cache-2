<?php declare(strict_types=1);
namespace Vendi\Cache;

class CacheStats
{
    private $_files = 0;
    private $_dirs = 0;
    private $_data = 0;
    private $_compressed_files = 0;
    private $_compressed_bytes = 0;
    private $_uncompressed_files = 0;
    private $_uncompressed_bytes = 0;
    private $_oldestFile = PHP_INT_MAX;
    private $_newestFile = 0;
    private $_largestFile = 0;

    public function get_files()
    {
        return $this->_files;
    }

    public function get_dirs()
    {
        return $this->_dirs;
    }

    public function get_data()
    {
        return $this->_data;
    }

    public function get_compressed_files()
    {
        return $this->_compressed_files;
    }

    public function get_compressed_bytes()
    {
        return $this->_compressed_bytes;
    }

    public function get_uncompressed_files()
    {
        return $this->_uncompressed_files;
    }

    public function get_uncompressed_bytes()
    {
        return $this->_uncompressed_bytes;
    }

    public function get_oldest_file()
    {
        return $this->_oldestFile;
    }

    public function get_newest_file()
    {
        return $this->_newestFile;
    }

    public function get_largest_file()
    {
        return $this->_largestFile;
    }

    public function increment_dir_count()
    {
        $this->_dirs++;
    }

    public function increment_file_count()
    {
        $this->_files++;
    }

    public function add_size_to_data($size)
    {
        $this->_data += $size;
    }

    public function increment_compressed_file_count()
    {
        $this->_compressed_files++;
    }

    public function increment_uncompressed_file_count()
    {
        $this->_uncompressed_files++;
    }

    public function add_bytes_to_compressed_file_size($size)
    {
        $this->_compressed_bytes += $size;
    }

    public function add_bytes_to_uncompressed_file_size($size)
    {
        $this->_uncompressed_bytes += $size;
    }

    public function maybe_set_largest_file_size($size)
    {
        $this->_largestFile = \max($this->_largestFile, $size);
    }

    public function maybe_set_oldest_file($ctime)
    {
        $this->_oldestFile = \min($this->_oldestFile, $ctime);
    }

    public function maybe_set_newest_file($ctime)
    {
        $this->_newestFile = \max($this->_newestFile, $ctime);
    }

    public function maybe_set_oldest_newest_file($ctime)
    {
        $this->maybe_set_oldest_file($ctime);
        $this->maybe_set_newest_file($ctime);
    }

    public static function generate_from_file_system(Maestro $maestro)
    {
        $logger = $maestro->get_logger();

        $logger->info('Request received to calculate cache stats');

        //Get our file system
        $fs = $maestro
                ->get_file_system()
            ;

        $files = $fs->get_directory_contents_abs($maestro->get_secretary()->get_cache_folder_abs());

        //Create a new instance of this class
        $obj = new self();

        //Loop through all files and dirs
        foreach ($files as $item) {

            //For dirs, we only record their count
            if ($item->isDir()) {
                $obj->increment_dir_count();
                continue;
            }

            //On the off-chance that there's other things (today or in the
            //future) we'll specifically handle only files below
            if ($item->isFile()) {

                //General observations on the current file
                $obj->increment_file_count();
                $obj->maybe_set_oldest_newest_file($item->getMTime());
                $obj->add_size_to_data($item->getSize());
                $obj->maybe_set_largest_file_size($item->getSize());

                switch ($item->getExtension()) {
                    case 'gz':
                        $obj->add_bytes_to_compressed_file_size($item->getSize());
                        $obj->increment_compressed_file_count();
                        break;

                    case 'html':
                        $obj->add_bytes_to_uncompressed_file_size($item->getSize());
                        $obj->increment_uncompressed_file_count();
                        break;

                    default:
                        $logger->info(
                                        'Unsupported file found in cache',
                                        [
                                            'file_info' => $item,
                                        ]
                                    );
                }

                continue;
            }

            $logger->info(
                            'Unsupported file type (not a file or a dir) in cache',
                            [
                                'file_info' => $item,
                            ]
                        );
        }

        $logger->info(
                        'Final cache stats',
                        [
                            'stats' => [
                                            'file_count'                => $obj->get_files(),
                                            'dir_count'                 => $obj->get_dirs(),
                                            'total_space'               => $obj->get_data(),
                                            'compressed_file_count'     => $obj->get_compressed_files(),
                                            'compressed_file_bytes'     => $obj->get_compressed_bytes(),
                                            'uncompressed_file_count'   => $obj->get_uncompressed_files(),
                                            'uncompressed_file_bytes'   => $obj->get_uncompressed_bytes(),
                                            'oldest_file'               => $obj->get_oldest_file(),
                                            'newest_file'               => $obj->get_newest_file(),
                                            'largest_file'              => $obj->get_largest_file(),
                            ]
                        ]
                    );

        return $obj;
    }
}
