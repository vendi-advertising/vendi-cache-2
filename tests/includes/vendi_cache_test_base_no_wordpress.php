<?php

declare(strict_types=1);

namespace Vendi\Cache\Tests;

/**
 * @coversNothing
 */
class vendi_cache_test_base_no_wordpress extends \PHPUnit_Framework_TestCase implements vendi_cache_test_base_interface
{
    use trait_test_logging;

    /**
     * PHPUnit 6+ compatibility shim.
     *
     * @param mixed      $exception
     * @param string     $message
     * @param int|string $code
     */
    public function setExpectedException($exception, $message = '', $code = null)
    {
        if (method_exists('PHPUnit_Framework_TestCase', 'setExpectedException')) {
            parent::setExpectedException($exception, $message, $code);
        } else {
            $this->expectException($exception);
            if ('' !== $message) {
                $this->expectExceptionMessage($message);
            }
            if (null !== $code) {
                $this->expectExceptionCode($code);
            }
        }
    }

    /**
     * Asserts that the contents of two un-keyed, single arrays are equal, without accounting for the order of elements.
     *
     * @since 3.5.0
     *
     * @param array $expected expected array
     * @param array $actual   array to check
     */
    public function assertEqualSets($expected, $actual)
    {
        sort($expected);
        sort($actual);
        $this->assertSame($expected, $actual);
    }
}
