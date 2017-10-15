<?php

namespace Vendi\Cache\Tests;

use Vendi\Cache\Maestro;

class vendi_cache_test_base extends \PHPUnit_Framework_TestCase
{
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
}
