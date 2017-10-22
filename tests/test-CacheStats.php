<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\Maestro;

class test_CacheStats extends vendi_cache_test_base
{
    /**
     * @dataProvider provider_get_all_increments
     * @covers Vendi\Cache\CacheStats::increment_dir_count
     * @covers Vendi\Cache\CacheStats::increment_file_count
     * @covers Vendi\Cache\CacheStats::increment_compressed_file_count
     * @covers Vendi\Cache\CacheStats::increment_uncompressed_file_count
     * @covers Vendi\Cache\CacheStats::get_dirs
     * @covers Vendi\Cache\CacheStats::get_files
     * @covers Vendi\Cache\CacheStats::get_compressed_files
     * @covers Vendi\Cache\CacheStats::get_uncompressed_files
     */
    public function test_all_increments( $property, $method )
    {
        $cs = new \Vendi\Cache\CacheStats();

        $property = "get_$property";

        $this->assertSame( 0, $cs->$property() );
        $cs->$method();
        $this->assertSame( 1, $cs->$property() );

    }

    /**
     * @dataProvider provider_get_all_adders
     * @covers Vendi\Cache\CacheStats::add_size_to_data
     * @covers Vendi\Cache\CacheStats::add_bytes_to_compressed_file_size
     * @covers Vendi\Cache\CacheStats::add_bytes_to_uncompressed_file_size
     * @covers Vendi\Cache\CacheStats::get_data
     * @covers Vendi\Cache\CacheStats::get_compressed_bytes
     * @covers Vendi\Cache\CacheStats::get_uncompressed_bytes
     */
    public function test_all_adders( $property, $method )
    {
        $cs = new \Vendi\Cache\CacheStats();

        $property = "get_$property";

        $this->assertSame( 0, $cs->$property() );
        $cs->$method( 100 );
        $this->assertSame( 100, $cs->$property() );
        $cs->$method( 200 );
        $this->assertSame( 300, $cs->$property() );

    }

    /**
     * @covers Vendi\Cache\CacheStats::maybe_set_oldest_newest_file
     * @covers Vendi\Cache\CacheStats::maybe_set_newest_file
     * @covers Vendi\Cache\CacheStats::maybe_set_oldest_file
     * @covers Vendi\Cache\CacheStats::get_oldest_file
     * @covers Vendi\Cache\CacheStats::get_newest_file
     */
    public function test_maybe_set_oldest_newest_file()
    {
        $cs = new \Vendi\Cache\CacheStats();

        //Check defaults
        $this->assertSame( PHP_INT_MAX, $cs->get_oldest_file() );
        $this->assertSame( 0, $cs->get_newest_file() );

        //First call, both oldest and newest should be the same
        $cs->maybe_set_oldest_newest_file( 400 );
        $this->assertSame( 400, $cs->get_oldest_file() );
        $this->assertSame( 400, $cs->get_newest_file() );

        //Set a newer file, oldest should stay the same
        $cs->maybe_set_oldest_newest_file( 500 );
        $this->assertSame( 400, $cs->get_oldest_file() );
        $this->assertSame( 500, $cs->get_newest_file() );

        //Set an older file, newest should stay the same
        $cs->maybe_set_oldest_newest_file( 300 );
        $this->assertSame( 300, $cs->get_oldest_file() );
        $this->assertSame( 500, $cs->get_newest_file() );
    }

    /**
     * @covers Vendi\Cache\CacheStats::maybe_set_largest_file_size
     * @covers Vendi\Cache\CacheStats::get_largest_file
     *
     */
    public function test_maybe_set_largest_file_size()
    {
        $cs = new \Vendi\Cache\CacheStats();

        //Check the default
        $this->assertSame( 0, $cs->get_largest_file() );

        //Set a higher value, expect change
        $cs->maybe_set_largest_file_size( 100 );
        $this->assertSame( 100, $cs->get_largest_file() );

        //Set a lower value, expect no change
        $cs->maybe_set_largest_file_size( 20 );
        $this->assertSame( 100, $cs->get_largest_file() );
    }

    /**
     * @coversNothing
     */
    public function test_NOTHING()
    {
        \Vendi\Cache\CacheStats::generate_from_file_system( $this->__get_new_maestro() );
        $this->assertTrue( true );
    }

    public function provider_get_all_increments()
    {
        return array(
                        array( 'dirs', 'increment_dir_count' ),
                        array( 'files', 'increment_file_count' ),
                        array( 'compressed_files', 'increment_compressed_file_count' ),
                        array( 'uncompressed_files', 'increment_uncompressed_file_count' ),
                );
    }

    public function provider_get_all_adders()
    {
        return array(
                        array( 'data', 'add_size_to_data' ),
                        array( 'compressed_bytes', 'add_bytes_to_compressed_file_size' ),
                        array( 'uncompressed_bytes', 'add_bytes_to_uncompressed_file_size' ),
                );
    }
}
