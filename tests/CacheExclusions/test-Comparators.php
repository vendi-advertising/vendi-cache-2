<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\CacheExclusions\Comparators\Contains;
use Vendi\Cache\CacheExclusions\Comparators\EndsWith;
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
     * @param mixed $ignore2
     * @param mixed $ignore3
     * @param mixed $source_string
     * @param mixed $test_string
     */
    public function test_MatchesExactly($ignore, $expected, $ignore2, $ignore3, $source_string, $test_string)
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
     * @param mixed $ignore2
     * @param mixed $ignore3
     * @param mixed $source_string
     * @param mixed $test_string
     */
    public function test_StartsWith($expected, $ignore, $ignore2, $ignore3, $source_string, $test_string)
    {
        $this->assertSame(
                            $expected,
                            (new StartsWith())
                                ->does_source_string_match_rule_for_test_string($source_string, $test_string)
                        );
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Comparators\EndsWith::does_source_string_match_rule_for_test_string
     * @dataProvider provider_for_test_all_comparators
     * @param mixed $ignore
     * @param mixed $ignore2
     * @param mixed $expected
     * @param mixed $ignore3
     * @param mixed $source_string
     * @param mixed $test_string
     */
    public function test_EndsWith($ignore, $ignore2, $expected, $ignore3, $source_string, $test_string)
    {
        $this->assertSame(
                            $expected,
                            (new EndsWith())
                                ->does_source_string_match_rule_for_test_string($source_string, $test_string)
                        );
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Comparators\Contains::does_source_string_match_rule_for_test_string
     * @dataProvider provider_for_test_all_comparators
     * @param mixed $ignore
     * @param mixed $ignore2
     * @param mixed $ignore3
     * @param mixed $expected
     * @param mixed $source_string
     * @param mixed $test_string
     */
    public function test_Contains($ignore, $ignore2, $ignore3, $expected, $source_string, $test_string)
    {
        $this->assertSame(
                            $expected,
                            (new Contains())
                                ->does_source_string_match_rule_for_test_string($source_string, $test_string)
                        );
    }

    /**
     * @covers \Vendi\Cache\CacheExclusions\Comparators\MatchesExactly::get_storage_name
     * @covers \Vendi\Cache\CacheExclusions\Comparators\StartsWith::get_storage_name
     * @covers \Vendi\Cache\CacheExclusions\Comparators\EndsWith::get_storage_name
     * @covers \Vendi\Cache\CacheExclusions\Comparators\Contains::get_storage_name
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
                    //StartsWith,   MatchesExactly,     EndsWith,   Contains,   Source, Test
                    [true,          true,               true,       true,       'abc', 'abc'],
                    [true,          false,              false,      true,       'abc', 'ab'],
                    [false,         false,              false,      false,      'ab', 'abc'],

                    //Leading space
                    [false,         false,              true,       true,       ' abc', 'abc'],
            ];
    }

    public function provider_for_test_known_storage_names()
    {
        return [
                    ['MatchesExactly', 'matches-exactly'],
                    ['StartsWith', 'starts-with'],
                    ['EndsWith', 'ends-with'],
                    ['Contains', 'contains'],
        ];
    }
}
