<?php declare(strict_types=1);
namespace Vendi\Cache\CacheExclusions\Sources;

final class UserAgentSource extends AbstractSource
{
    final public function get_storage_name()
    {
        return 'user-agent';
    }

    final public function get_user_agent()
    {
        $request = $this->get_maestro()->get_request();
        if (!$request->hasHeader('HTTP_USER_AGENT')) {
            return null;
        }

        $ua = $request->getHeader('HTTP_USER_AGENT');
        if (1===count($ua)) {
            return reset($ua);
        }
        return null;
    }

    final public function should_request_be_excluded_from_caching(AbstractComparator $comparator, $string_to_test)
    {
        $ua = $this->get_user_agent();
        if (!$ua) {
            return false;
        }
        if ($comparator->does_source_string_match_rule_for_test_string($ua, $string_to_test)) {
            return true;
        }
        return false;
    }
}
