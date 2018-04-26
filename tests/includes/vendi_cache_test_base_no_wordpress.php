<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use GuzzleHttp\Psr7\ServerRequest;
use org\bovigo\vfs\vfsStream;
use Vendi\Cache\Maestro;
use Vendi\Cache\Secretary;
use Webmozart\PathUtil\Path;

/**
 * @coversNothing
 */
class vendi_cache_test_base_no_wordpress extends \PHPUnit_Framework_TestCase
{
    public function __create_server_request_with_custom_headers(array $headers = [])
    {
        $merged = array_merge($_SERVER, $headers);
        return ( new ServerRequest('GET', 'http://www.example.net', [], null, '1.1', $merged) );
    }

    public function __create_server_request_from_url($url, $method = 'GET', array $cookies = [])
    {
        //This is dumb but whatever. As far as I can tell, Guzzle ignores the query string
        //for ServerRequest when manually creating an instance.
        $query_as_string = parse_url($url, PHP_URL_QUERY);
        $query_as_array = [];
        if ($query_as_string) {
            parse_str($query_as_string, $query_as_array);
        }
        return ( new ServerRequest($method, $url) )
                ->withCookieParams($cookies)
                ->withQueryParams($query_as_array)
            ;
    }

    /**
     * Create a new maestro for all tests with optional parameters.
     * @param  ServerRequest|null $request         A specific Guzzle Request or null
     *                                             for the default Request
     * @param  callable|null      $handle_function The function to invoke for each
     *                                             log call or null for none
     * @param  Secretary|null     $secretary       A customized secretary to use. Never use unless you are actually
     *                                             testing a specific Secretary.
     * @return Maestro
     */
    public function __get_new_maestro(ServerRequest $request = null, callable $handle_function = null, Secretary $secretary = null)
    {
        $maestro = new Maestro();

        if (! $secretary) {
            $secretary = new \Vendi\Cache\Tests\non_global_constant_secretary();
        }

        if (!$handle_function) {
            $handle_function = [$this, '_handle_logger'];
        }

        return $maestro
                ->with_secretary($secretary)
                ->with_request($request ? $request : Maestro::get_default_request())
                ->with_logger(
                                new \Monolog\Logger(
                                                'vendi-cache-noop',
                                                [
                                                    new nullhandler_log_handler($handle_function)
                                                ]
                                            )
                 )
            ;
    }

    /**
     * Determine if two associative arrays are similar.
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering.
     *
     * @see  https://stackoverflow.com/a/3843768/231316
     *
     * @param  array $a
     * @param  array $b
     * @return bool
     */
    public function arrays_are_similar($a, $b)
    {
        // if the indexes don't match, return immediately
        if (count(array_diff_assoc($a, $b))) {
            return false;
        }

        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach ($a as $k => $v) {
            if ($v !== $b[ $k ]) {
                return false;
            }
        }

        // we have identical indexes, and no unequal values
        return true;
    }
}
