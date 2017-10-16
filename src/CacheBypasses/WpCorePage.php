<?php

namespace Vendi\Cache\CacheBypasses;

final class WpCorePage extends AbstractCacheBypass
{
    public function is_cacheable()
    {
        $no_cache_pages = [
                            '/wp-login.php',
                            '/wp-signup.php',
                            '/wp-trackback.php',
                            '/xmlrpc.php',
                        ];

        $this_page = $this->get_path_url();

        foreach ($no_cache_pages as $page) {
            if ($page == $this_page) {
                $this->log_request_as_not_cacheable(
                                                        [
                                                            'reason' => 'Special WordPress page detected',
                                                            'page'   => $page,
                                                        ]
                                                );
                return false;
            }
        }

        return true;
    }
}
