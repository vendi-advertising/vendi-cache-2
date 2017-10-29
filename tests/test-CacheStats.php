<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use org\bovigo\vfs\vfsStream;
use Webmozart\PathUtil\Path;

/**
 * @group CacheStats
 */
class test_CacheStats extends vendi_cache_test_base
{
    /**
     * @dataProvider provider_get_all_increments
     * @covers \Vendi\Cache\CacheStats::increment_dir_count
     * @covers \Vendi\Cache\CacheStats::increment_file_count
     * @covers \Vendi\Cache\CacheStats::increment_compressed_file_count
     * @covers \Vendi\Cache\CacheStats::increment_uncompressed_file_count
     * @covers \Vendi\Cache\CacheStats::get_dirs
     * @covers \Vendi\Cache\CacheStats::get_files
     * @covers \Vendi\Cache\CacheStats::get_compressed_files
     * @covers \Vendi\Cache\CacheStats::get_uncompressed_files
     * @param mixed $property
     * @param mixed $method
     */
    public function test_all_increments($property, $method)
    {
        $cs = new \Vendi\Cache\CacheStats();

        $property = "get_$property";

        $this->assertSame(0, $cs->$property());
        $cs->$method();
        $this->assertSame(1, $cs->$property());
    }

    /**
     * @dataProvider provider_get_all_adders
     * @covers \Vendi\Cache\CacheStats::add_size_to_data
     * @covers \Vendi\Cache\CacheStats::add_bytes_to_compressed_file_size
     * @covers \Vendi\Cache\CacheStats::add_bytes_to_uncompressed_file_size
     * @param mixed $property
     * @param mixed $method
     */
    public function test_all_adders($property, $method)
    {
        $cs = new \Vendi\Cache\CacheStats();

        $property = "get_$property";

        $this->assertSame(0, $cs->$property());
        $cs->$method(100);
        $this->assertSame(100, $cs->$property());
        $cs->$method(200);
        $this->assertSame(300, $cs->$property());
    }

    /**
     * @covers \Vendi\Cache\CacheStats::maybe_set_oldest_newest_file
     * @covers \Vendi\Cache\CacheStats::maybe_set_newest_file
     * @covers \Vendi\Cache\CacheStats::maybe_set_oldest_file
     * @covers \Vendi\Cache\CacheStats::get_oldest_file
     * @covers \Vendi\Cache\CacheStats::get_newest_file
     */
    public function test_maybe_set_oldest_newest_file()
    {
        $cs = new \Vendi\Cache\CacheStats();

        //Check defaults
        $this->assertSame(PHP_INT_MAX, $cs->get_oldest_file());
        $this->assertSame(0, $cs->get_newest_file());

        //First call, both oldest and newest should be the same
        $cs->maybe_set_oldest_newest_file(400);
        $this->assertSame(400, $cs->get_oldest_file());
        $this->assertSame(400, $cs->get_newest_file());

        //Set a newer file, oldest should stay the same
        $cs->maybe_set_oldest_newest_file(500);
        $this->assertSame(400, $cs->get_oldest_file());
        $this->assertSame(500, $cs->get_newest_file());

        //Set an older file, newest should stay the same
        $cs->maybe_set_oldest_newest_file(300);
        $this->assertSame(300, $cs->get_oldest_file());
        $this->assertSame(500, $cs->get_newest_file());
    }

    /**
     * @covers \Vendi\Cache\CacheStats::maybe_set_largest_file_size
     * @covers \Vendi\Cache\CacheStats::get_largest_file
     */
    public function test_maybe_set_largest_file_size()
    {
        $cs = new \Vendi\Cache\CacheStats();

        //Check the default
        $this->assertSame(0, $cs->get_largest_file());

        //Set a higher value, expect change
        $cs->maybe_set_largest_file_size(100);
        $this->assertSame(100, $cs->get_largest_file());

        //Set a lower value, expect no change
        $cs->maybe_set_largest_file_size(20);
        $this->assertSame(100, $cs->get_largest_file());
    }

    /**
     * @group  CacheStats__generate_from_file_system
     * @covers \Vendi\Cache\CacheStats::generate_from_file_system
     * @covers \Vendi\Cache\CacheStats::get_dirs
     * @covers \Vendi\Cache\CacheStats::get_files
     * @covers \Vendi\Cache\CacheStats::get_compressed_files
     * @covers \Vendi\Cache\CacheStats::get_uncompressed_files
     * @covers \Vendi\Cache\CacheStats::get_uncompressed_bytes
     * @covers \Vendi\Cache\CacheStats::get_compressed_bytes
     * @covers \Vendi\Cache\CacheStats::get_oldest_file
     * @covers \Vendi\Cache\CacheStats::get_newest_file
     * @covers \Vendi\Cache\CacheStats::get_largest_file
     * @covers \Vendi\Cache\CacheStats::get_data
     */
    public function test_generate_from_file_system()
    {
        $maestro = $this->__get_new_maestro();

        $local_folder = 'wp-content/vendi_cache/cheese';

        //Create the full path to this directory in the VFS
        vfsStream::newDirectory($local_folder)
            ->at($this->get_vfs_root())
        ;

        //Files for testing, path => bytes
        //Makes sure to update assert's below if you change these
        $files = [
                    'small.html' => 1234,
                    'small.html.gz' => 50,

                    'large.html' => 12344567,
                    'large.html.gz' => 5056,

                    'what_the_heck.pdf' => 5678,
            ];

        //Create the files above and sanity check that they actually exist before
        //passing them onto the stat generator
        foreach ($files as $file => $byte_count) {
            //We're joining multiple paths and since WebMozart supports this just us it
            $abs = Path::join(vfsStream::url($this->get_root_dir_name_no_trailing_slash()), $local_folder, $file);

            //The file shouldn't exist by default
            $this->assertFileNotExists($abs);

            //Create the file, filling it with whatever we want
            file_put_contents($abs, str_repeat('z', $byte_count));

            //Make sure that it exists before moving up
            $this->assertFileExists($abs);
        }

        $stats = \Vendi\Cache\CacheStats::generate_from_file_system($maestro);

        $this->assertSame(1234 + 12344567, $stats->get_uncompressed_bytes());
        $this->assertSame(50 + 5056, $stats->get_compressed_bytes());
        $this->assertSame(2, $stats->get_uncompressed_files());
        $this->assertSame(2, $stats->get_compressed_files());

        $this->assertSame(5, $stats->get_files());
        $this->assertSame(1, $stats->get_dirs());



        $this->assertSame(array_sum($files), $stats->get_data());
    }

    public function provider_get_all_increments()
    {
        return [
                        [ 'dirs', 'increment_dir_count' ],
                        [ 'files', 'increment_file_count' ],
                        [ 'compressed_files', 'increment_compressed_file_count' ],
                        [ 'uncompressed_files', 'increment_uncompressed_file_count' ],
                ];
    }

    public function provider_get_all_adders()
    {
        return [
                        [ 'data', 'add_size_to_data' ],
                        [ 'compressed_bytes', 'add_bytes_to_compressed_file_size' ],
                        [ 'uncompressed_bytes', 'add_bytes_to_uncompressed_file_size' ],
                ];
    }
}
