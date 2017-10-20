<?php

namespace Vendi\Cache\CacheBypasses;

use Assert\Assertion;
use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;

abstract class AbstractCacheBypass
{
    private $_request;

    private $_logger;

    private $_maestro;

    abstract public function is_resource_not_cacheable();

    public function __construct(Maestro $maestro)
    {
        $this->_maestro = $maestro;
    }

    /**
     * Get the Maestro associated with the current request.
     * @return Maestro
     */
    final public function get_maestro()
    {
        return $this->_maestro;
    }

    /**
     * Get the cache settings associated with the current request.
     * @return Secretary
     */
    final public function get_secretary()
    {
        return $this->get_maestro()->get_secretary();
    }

    final public function get_url()
    {
        return $this->get_maestro()->get_request()->getUri();
    }

    final public function get_query_string()
    {
        return $this->get_maestro()->get_request()->getQueryString();
    }

    final public function get_method()
    {
        return $this->get_maestro()->get_request()->getMethod();
    }

    final public function get_path_url()
    {
        //TODO: I'm not 100% sure this is right
        //https://github.com/symfony/http-foundation/blob/3.4/Request.php#L982
        $request = $this->get_maestro()->get_request();
        return $request->getBaseUrl() . $request->getPathInfo();
    }

    final public function get_cookies()
    {
        return $this->get_maestro()->get_request()->cookies;
    }

    final public function log_request_as_not_cacheable(array $args)
    {
        $this->get_maestro()->get_logger()->debug('Request not cacheable', $args);
    }
}
