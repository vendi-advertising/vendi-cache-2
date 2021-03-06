<?php declare(strict_types=1);
namespace Vendi\Cache\CacheBypasses;

final class WpCookies extends AbstractCacheBypass
{
    public function is_resource_not_cacheable()
    {
        $client_cookies = $this->get_cookies();

        //Return early for Humbug mutant testing.
        if (0===\count($client_cookies)) {
            return false;
        }

        $cookies_to_test = [
                                'comment_author',
                                'wp-postpass',
                                'wf_logout',
                                'wordpress_logged_in',
                                'wptouch_switch_toggle',
                                'wpmp_switcher',
                        ];

        foreach ($client_cookies as $client_cookie => $value) {
            foreach ($cookies_to_test as $cookie_to_test) {
                //contains a cookie which indicates user must not be cached
                if (\mb_strpos($client_cookie, $cookie_to_test) !== false) {
                    $this->log_request_as_not_cacheable(
                                                            [
                                                                'reason' => 'Found special cookie',
                                                                'cookie' => $cookie_to_test,
                                                                'value'  => $value,
                                                            ]
                                                    );
                    return true;
                }
            }
        }

        return false;
    }
}
