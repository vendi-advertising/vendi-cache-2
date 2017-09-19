<?php

namespace Vendi\Cache\CacheBypasses;

final class WpCookies extends AbstractCacheBypass
{
    public function is_cacheable( )
    {

        $client_cookies = $this->get_cookies();

        //wordpress_logged_in_[hash] cookies indicates logged in
        if( is_array( $client_cookies ) && count( $client_cookies ) > 0 )
        {
            $cookies_to_test = [
                                    'comment_author',
                                    'wp-postpass',
                                    'wf_logout',
                                    'wordpress_logged_in',
                                    'wptouch_switch_toggle',
                                    'wpmp_switcher',
                            ];

            foreach( array_keys( $client_cookies ) as $client_cookie )
            {
                foreach( $cookies_to_test as $cookie_to_test )
                {
                    //contains a cookie which indicates user must not be cached
                    if( strpos( $client_cookie, $cookie_to_test ) !== false )
                    {
                        $this->log_request_as_not_cacheable(
                                                                [
                                                                    'reason' => 'Found special cookie',
                                                                    'cookie' => $cookie_to_test,
                                                                    'value'  => $client_cookies[ $client_cookie ],
                                                                ]
                                                        );
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
