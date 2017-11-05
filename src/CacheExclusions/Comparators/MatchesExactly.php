<?php declare(strict_types=1);
namespace Vendi\Cache\CacheExclusions\Comparators;

final class MatchesExactly extends AbstractComparator
{
    final public function get_storage_name()
    {
        return 'matches-exactly';
    }

    final public function does_source_string_match_rule_for_test_string($source_string, $test_string)
    {
        return $source_string === $test_string;
    }
}
