<?php

namespace Vendi\Cache\CacheBypasses;

final class QueryString extends AbstractCacheBypass
{
    public function is_resource_not_cacheable()
    {
        //TODO: Cache resources if we have a DDOS style querystring? Original code was:
        /*
            //Don't cache query strings unless they are /?123132423=123123234 DDoS style.
            if( strlen( $query_string ) > 0 && ( ! preg_match( '/^\d+=\d+$/', $query_string ) ) )
            {
                return false;
            }
        */

        $query_string = $this->get_query_string();

        if (strlen($query_string) > 0) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason'       => 'Query string found',
                                                        'query_string' => $query_string,
                                                    ]
                                            );
            return true;
        }

        return false;
    }
}
