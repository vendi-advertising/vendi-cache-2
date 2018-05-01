<?php declare(strict_types=1);
namespace Vendi\Cache\CacheExclusions\Comparators;

final class Contains extends AbstractComparator
{
    final public function get_storage_name()
    {
        return 'contains';
    }

    final public function does_source_string_match_rule_for_test_string($source_string, $test_string)
    {
        if (\mb_strpos($source_string, $test_string) !== false) {
            return true;
        }

        return false;
    }
}
