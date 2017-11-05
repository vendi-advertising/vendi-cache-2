<?php declare(strict_types=1);
namespace Vendi\Cache\CacheExclusions\Comparators;

use Vendi\Cache\CacheExclusions\AbstractCacheExclusion;

abstract class AbstractComparator extends AbstractCacheExclusion
{
    abstract public function does_source_string_match_rule_for_test_string($source_string, $test_string);
}
