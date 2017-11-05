<?php declare(strict_types=1);
namespace Vendi\Cache\CacheExclusions\Comparators;

final class StartsWith extends AbstractComparator
{
    final public function get_storage_name()
    {
        return 'starts-with';
    }

    final public function does_source_string_match_rule_for_test_string($source_string, $test_string)
    {
        if (mb_substr($source_string, 0, mb_strlen($test_string)) === $test_string) {
            return true;
        }

        return false;
    }
}
