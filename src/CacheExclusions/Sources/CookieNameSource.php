<?php declare(strict_types=1);
namespace Vendi\Cache\CacheExclusions\Sources;

use Vendi\Cache\CacheExclusions\Comparators\AbstractComparator;

final class CookieNameSource extends AbstractSource
{
    final public function get_storage_name()
    {
        return 'cookie-name';
    }

    final public function get_cookies()
    {
        return $this->get_maestro()->get_request()->getCookieParams();
    }

    final public function should_request_be_excluded_from_caching(AbstractComparator $comparator, $string_to_test)
    {
        $cookies = $this->get_cookies();
        if (! $cookies || ! is_array($cookies)) {
            return false;
        }

        foreach ($cookies as $name => $value) {
            if ($comparator->does_source_string_match_rule_for_test_string($name, $string_to_test)) {
                return true;
            }
        }

        return false;
    }
}
