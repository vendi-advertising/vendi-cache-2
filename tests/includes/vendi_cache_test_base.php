<?php

namespace Vendi\Cache\Tests;

use League\Flysystem\Adapter\Local;
use Monolog\Handler\NullHandler;
use Vendi\Cache\Maestro;

class vendi_cache_test_base extends \PHPUnit_Framework_TestCase
{
    public function _get_new_maestro()
    {
        return new Maestro();
    }

    public function _get_maestro_with_custom_filesystem( $dir )
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

        return $this->_get_new_maestro()
                        ->with_file_system_adapter( $adapter )
                        ->with_logger(
                                        new \Monolog\Logger(
                                                        'vendi-cache-noop',
                                                        array( new NullHandler( ) )
                                                    )
                         )
                    ;
    }

    /**
     * PHPUnit 6+ compatibility shim.
     *
     * @see https://github.com/WordPress/wordpress-develop/blob/master/tests/phpunit/includes/testcase.php#L446
     *
     * @param mixed      $exception
     * @param string     $message
     * @param int|string $code
     */
    public function setExpectedException( $exception, $message = '', $code = null )
    {
        if ( method_exists( 'PHPUnit_Framework_TestCase', 'setExpectedException' ) )
        {
            parent::setExpectedException( $exception, $message, $code );
        }
        else
        {
            $this->expectException( $exception );
            if ( '' !== $message )
            {
                $this->expectExceptionMessage( $message );
            }
            if ( null !== $code )
            {
                $this->expectExceptionCode( $code );
            }
        }
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
