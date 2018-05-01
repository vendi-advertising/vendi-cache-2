<?php declare(strict_types=1);
namespace Vendi\Cache\CacheExclusions\Comparators;

final class EndsWith extends AbstractComparator
{
    final public function get_storage_name()
    {
        return 'ends-with';
    }

    /**
     * @see https://stackoverflow.com/a/619725/231316
     * @param mixed $string
     * @param mixed $other
     * @param mixed $source_string
     * @param mixed $test_string
     */
    final public function does_source_string_match_rule_for_test_string($source_string, $test_string)
    {
        $strlen = \mb_strlen($source_string);
        $testlen = \mb_strlen($test_string);
        if ($testlen > $strlen) {
            return false;
        }

        if (\substr_compare($source_string, $test_string, $strlen - $testlen, $testlen) === 0) {
            return true;
        }

        return false;
    }
}
