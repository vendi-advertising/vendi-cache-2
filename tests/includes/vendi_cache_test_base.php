<?php

namespace Vendi\Cache\Tests;

use League\Flysystem\Adapter\Local;
use Monolog\Handler\NullHandler;
use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\Maestro;
use Vendi\Cache\Tests\nullhandler_log_handler;

class vendi_cache_test_base extends \WP_UnitTestCase
{
    private $_dirs = array();

    private $_files = array();

    public function tearDown()
    {
        parent::tearDown();

        foreach( $this->_files as $f )
        {
            if( is_file( $f ) )
            {
                unlink( $f );
            }
        }

        foreach( $this->_dirs as $d )
        {
            if( is_dir( $d ) )
            {
                rmdir( $d );
            }
        }
    }

    public function touch_file( $path )
    {
        touch( $path );
        $this->_files[] = $path;
    }

    //https://stackoverflow.com/a/1707859/231316
    public function create_temp_dir()
    {
        $tempfile = tempnam( sys_get_temp_dir(), 'VC2' );
        if( false === $tempfile )
        {
            throw new \Exception( 'Could not create file for temporary directory' );
        }

        if( file_exists( $tempfile ) )
        {
            unlink( $tempfile );
        }

        mkdir( $tempfile );

        if( ! is_dir( $tempfile ) )
        {
            throw new \Exception( 'Could not create temporary directory' );
        }

        $this->_dirs[] = $tempfile;

        return $tempfile;
    }

    public function __get_new_maestro( Request $request = null, callable $handle_function = null, $dir = null )
    {
        $adapter = null;
        if( $dir )
        {
            $adapter = new Local(
                                    $dir,

                                    //Use locks during write (default)
                                    LOCK_EX,

                                    //Throw exception on symlinks (default)
                                    Local::DISALLOW_LINKS,

                                    //Special file system permissions
                                    [
                                        'file' =>
                                                    [
                                                        'public'  => 0664,
                                                        'private' => 0664,
                                                    ],
                                        'dir' =>
                                                    [
                                                        'public'  => 0777,
                                                        'private' => 0777,
                                                    ]
                                    ]
                                );
        }

        $secretary = new \Vendi\Cache\Tests\non_global_constant_secretary();

        $secretary->set_constant( 'ABSPATH',        ABSPATH );
        $secretary->set_constant( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );

        return ( new Maestro() )
                ->with_secretary( $secretary )
                ->with_request(             $request ? $request : Maestro::get_default_request() )
                ->with_file_system_adapter( $adapter ? $adapter : Maestro::get_default_adapter( $secretary ) )
                ->with_logger(
                                new \Monolog\Logger(
                                                'vendi-cache-noop',
                                                [
                                                    new nullhandler_log_handler( $handle_function )
                                                ]
                                            )
                 )
            ;
    }

     /**
     * Determine if two associative arrays are similar
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering.
     *
     * @see  https://stackoverflow.com/a/3843768/231316
     *
     * @param array $a
     * @param array $b
     * @return bool
     */
    public function arrays_are_similar( $a, $b )
    {
        // if the indexes don't match, return immediately
        if( count( array_diff_assoc( $a, $b ) ) )
        {
            return false;
        }

        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach( $a as $k => $v )
        {
            if( $v !== $b[ $k ] )
            {
                return false;
            }
        }

        // we have identical indexes, and no unequal values
        return true;
    }
}
