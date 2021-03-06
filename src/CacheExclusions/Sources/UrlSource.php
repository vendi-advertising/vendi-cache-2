<?php declare(strict_types=1);
namespace Vendi\Cache\CacheExclusions\Sources;

use Vendi\Cache\CacheExclusions\Comparators\AbstractComparator;

final class UrlSource extends AbstractSource
{
    final public function get_storage_name()
    {
        return 'url';
    }

    final public function get_url_path()
    {
        $path = $this->get_maestro()->get_request()->getUri()->getPath();
        if (!$path) {
            $path = '/';
        }
        return $path;
    }

    final public function should_request_be_excluded_from_caching(AbstractComparator $comparator, $string_to_test)
    {
        return $comparator->does_source_string_match_rule_for_test_string($this->get_url_path(), $string_to_test);
    }
}
