<?php

namespace Vendi\Cache\CacheBypasses;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractCacheBypass implements CacheBypassInterface
{
    private $_request;

    private $_logger;

    final function __construct( Request $request, LoggerInterface $logger )
    {
        $this->_request = $request;
        $this->_logger  = $logger;
    }

    final public function get_url( )
    {
        return $this->_request->getUri();
    }

    final public function get_query_string( )
    {
        return $this->_request->getQueryString();
    }

    final public function get_method( )
    {
        return $this->_request->getMethod();
    }

    final public function get_path_url( )
    {
        //TODO: I'm not 100% sure this is right
        //https://github.com/symfony/http-foundation/blob/3.4/Request.php#L982
        return$this->_request->getBaseUrl();
    }

    final public function get_cookies( )
    {
        return $this->_request->cookies;
    }

    final public function log_request_as_not_cacheable( array $args )
    {
        $this->_logger->debug( 'Request not cacheable', $args );
    }
}
