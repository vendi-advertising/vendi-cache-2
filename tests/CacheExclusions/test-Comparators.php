<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheExclusions\Comparators\MatchesExactly;
use Vendi\Cache\CacheExclusions\Comparators\StartsWith;

/**
 * @group CacheExclusions
 */
class test_Comparators extends vendi_cache_test_base
{
    /**
     * @covers \Vendi\Cache\CacheExclusions\Comparators\MatchesExactly::does_source_string_match_rule_for_test_string
     * @dataProvider provider_for_test_all_comparators
     * @param mixed $ignore
     * @param mixed $expected
     * @param mixed $source_string
     * @param mixed $test_string
     */
    public function test_MatchesExactly($ignore, $expected, $source_string, $test_string)
    {
        $this->assertSame(
                            $expected,
                            (new MatchesExactly())
                                ->does_source_string_match_rule_for_test_string($source_string, $test_string)
                        );
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Comparators\StartsWith::does_source_string_match_rule_for_test_string
     * @dataProvider provider_for_test_all_comparators
     * @param mixed $expected
     * @param mixed $ignore
     * @param mixed $source_string
     * @param mixed $test_string
     */
    public function test_StartsWith($expected, $ignore, $source_string, $test_string)
    {
        $this->assertSame(
                            $expected,
                            (new StartsWith())
                                ->does_source_string_match_rule_for_test_string($source_string, $test_string)
                        );
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Comparators\MatchesExactly::get_storage_name
     * @dataProvider provider_for_test_known_storage_names
     * @param mixed $class_name
     * @param mixed $storage_name
     */
    public function test_known_storage_names($class_name, $storage_name)
    {
        $root_namespace       = 'Vendi\Cache';
        $exclusion_namespace  = "$root_namespace\\CacheExclusions";
        $comparator_namespace = "$exclusion_namespace\\Comparators";
        $class_name           = "$comparator_namespace\\$class_name";
        $obj = new $class_name();
        $this->assertSame($storage_name, $obj->get_storage_name());
    }

    public function provider_for_test_all_comparators()
    {
        return [
                    //StartsWith, MatchesExactly, Source, Test
                    [true,  true, 'abc', 'abc'],
                    [true,  false, 'abc', 'ab'],
                    [false, false, 'ab', 'abc'],

                    //Leading space
                    [false, false, ' abc', 'abc'],
            ];
    }

    public function provider_for_test_known_storage_names()
    {
        return [
                    ['MatchesExactly', 'matches-exactly'],
        ];
    }
}
