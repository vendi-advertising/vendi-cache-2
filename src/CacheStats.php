<?php

namespace Vendi\Cache;

use Assert\Assertion;
use League\Flysystem\Plugin\ListWith;
use Psr\Log\LogLevel;
use Vendi\Cache\Maestro;
use Vendi\Cache\CacheOptions\CacheOptionInterface;
use \Webmozart\PathUtil\Path;

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

    public function add_size_to_data( $size )
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

    public function add_bytes_to_compressed_file_size( $size )
    {
        $this->_compressed_bytes += $size;
    }

    public function add_bytes_to_uncompressed_file_size( $size )
    {
        $this->_uncompressed_bytes += $size;
    }

    public function maybe_set_largest_file_size( $size )
    {
        $this->_largestFile = max( $this->_largestFile, $size );
    }

    public function maybe_set_oldest_file( $ctime )
    {
        $this->_oldestFile = min( $this->_oldestFile, $ctime );
    }

    public function maybe_set_newest_file( $ctime )
    {
        $this->_newestFile = max( $this->_newestFile, $ctime );
    }

    public function maybe_set_oldest_newest_file( $ctime )
    {
        $this->maybe_set_oldest_file( $ctime );
        $this->maybe_set_newest_file( $ctime );
    }

    public static function generate_from_file_system( Maestro $maestro )
    {

        $logger = $maestro->get_logger();

        $logger->info( 'Request received to calculate cache stats' );

        //Get our file system
        $fs = $maestro
                ->get_file_system()
            ;

        //We need this plugin to get the listWith command to get additional data
        $fs
            ->addPlugin( new ListWith() )
        ;

        //Get all items recursively
        $items = $fs->listWith([ 'mimetype', 'size', 'timestamp'], '', true );

        //Get the log file so that we can exclude it
        $log_file_abs = Path::canonicalize( $maestro->get_secretary()->get_log_file_abs() );

        //Get the abs path to the cache folder to that we can prepend it to the
        //individual item to get the full path of the file. Weird, I know, but
        //FlySystem is a relative-based system only.
        $cache_folder = $maestro->get_secretary()->get_cache_folder_abs();

        //Create a new instance of this class
        $obj = new self();

        $found_log_file = false;

        //Loop through all files and dirs
        foreach( $items as $item )
        {
            //For dirs, we only record their count
            if( 'dir' === $item[ 'type' ] )
            {
                $obj->increment_dir_count();
                continue;
            }

            //On the off-chance that there's other things (today or in the
            //future) we'll specifically handle only file below
            if( 'file' === $item[ 'type' ] )
            {

                if( ! $found_log_file )
                {
                    //This is the ABS path to the file
                    $test_file_path = Path::join(
                                                    $cache_folder,
                                                    $item[ 'path' ]
                                                );

                    //If the current file is the log file
                    if( $test_file_path === $log_file_abs )
                    {
                        //Flag that we found it so that subsequent passes don't
                        //need to do this
                        $found_log_file = true;

                        $logger->info( 'Skipping log file' );
                        //Skip it
                        continue;
                    }
                }

                //General observations on the current file
                $obj->increment_file_count();
                $obj->maybe_set_oldest_newest_file( $item[ 'timestamp' ] );
                $obj->add_size_to_data( (int)$item[ 'size' ] );
                $obj->maybe_set_largest_file_size( (int)$item[ 'size' ] );

                //
                switch( $item[ 'mimetype' ] )
                {
                    case 'application/x-gzip':
                        $obj->add_bytes_to_compressed_file_size( (int)$item[ 'size' ] );
                        $obj->increment_compressed_file_count();
                        break;

                    case 'text/html':
                        $obj->add_bytes_to_uncompressed_file_size( (int)$item[ 'size' ] );
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
