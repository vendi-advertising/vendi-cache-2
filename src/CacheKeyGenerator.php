<?php

namespace Vendi\Cache;

use Assert\Assertion;

class CacheKeyGenerator
{
    private $_urls_to_files = array();

    private $_urls_to_files_cache_lookups = array();

    private $_maestro;

    public function __construct(Maestro $maestro)
    {
        $this->_maestro = $maestro;
    }

    /**
     * [get_maestro description]
     * @return Maestro
     */
    public function get_maestro()
    {
        return $this->_maestro;
    }

    /**
     * [get_mapping_of_urls_to_files description]
     * @return array
     */
    public function get_mapping_of_urls_to_files()
    {
        //Prove that our property is always an array
        Assertion::isArray($this->_urls_to_files);

        return $this->_urls_to_files;
    }

    /**
     * [get_url_without_scheme_and_host description]
     * @return string
     */
    public function get_url_without_scheme_and_host()
    {
        $request = $this->get_maestro()->get_request();

        $ret = $request->getUri()->getPath();
        if ('' === $ret) {
            $ret = '/';
        }

        return $ret;
    }

    /**
     * [get_cache_lookup_counts_for_url description]
     * @return string
     */
    public function get_cache_lookup_counts_for_url()
    {
        $url = $this->get_maestro()->get_request()->getUri()->__toString();

        //A non-null string is required
        Assertion::string($url);
        Assertion::notEmpty($url);

        if (! array_key_exists($url, $this->_urls_to_files_cache_lookups)) {
            return -1;
        }

        //The cache lookup should always be an int
        Assertion::integer($this->_urls_to_files_cache_lookups[ $url ]);

        return $this->_urls_to_files_cache_lookups[ $url ];
    }

    /**
     * [sanitize_host_for_cache_filename description]
     * @return string
     */
    public function sanitize_host_for_cache_filename()
    {
        $host = $this->get_maestro()->get_request()->getUri()->getHost();
        //A non-null string is required
        Assertion::string($host);
        Assertion::notEmpty($host);

        $ret = preg_replace('/[^a-zA-Z0-9\-\.]+/', '', $host);

        //The replacement must give us something to work with
        Assertion::notEmpty($host);

        return $ret;
    }

    /**
     * Return a file-system-local file name based on the given URL or global
     * HTTP Request variables.

     * @return string                       A path to the local file for the given request.
     */
    public function local_cache_filename_from_url()
    {
        $url = $this->get_maestro()->get_request()->getUri()->__toString();

        Assertion::string($url);
        Assertion::notEmpty($url);

        //See if we've previously determined the file for this URL
        if (array_key_exists($url, $this->_urls_to_files)) {
            //Increment a shared global counter, used for testing
            $this->_urls_to_files_cache_lookups[ $url ] += 1;

            $ret = $this->_urls_to_files[ $url ];

            Assertion::string($ret);
            Assertion::notEmpty($ret);

            return $ret;
        }

        $parts = parse_url($url);

        $host = $this->sanitize_host_for_cache_filename();
        $path = $this->sanitize_path_for_cache_filename();
        $ext = '';
        if ('HTTPS' === strtoupper($this->get_maestro()->get_request()->getUri()->getScheme())) {
            $ext = '_https';
        }

        $file = sprintf(
                            '%1$s_%2$s_%3$s%4$s.html',
                            $host,
                            $path,
                            'vendi_cache',
                            $ext
                        );

        //Cache this url and file for future use
        $this->_urls_to_files[ $url ] = $file;

        //Create an entry in the global counters for this URL
        $this->_urls_to_files_cache_lookups[ $url ] = 0;

        return $file;
    }

    /**
     * [sanitize_path_for_cache_filename description]
     * @return string
     */
    public function sanitize_path_for_cache_filename()
    {
        $path = $this->get_url_without_scheme_and_host();

        Assertion::string($path);
        Assertion::notEmpty($path);

        //Strip out bad chars and multiple dots
        $path = preg_replace('/(?:[^a-zA-Z0-9\-\_\.\~\/]+|\.{2,})/', '', $path);

        if (preg_match('/\/*([^\/]*)\/*([^\/]*)\/*([^\/]*)\/*([^\/]*)\/*([^\/]*)(.*)$/', $path, $matches)) {
            $path = $matches[ 1 ] . '/';
            for ($i = 2; $i <= 6; $i++) {
                $path .= strlen($matches[ $i ]) > 0 ? $matches[ $i ] : '';
                $path .= $i < 6 ? '~' : '';
            }
        }

        Assertion::notEmpty($path);
        return $path;
    }
}
