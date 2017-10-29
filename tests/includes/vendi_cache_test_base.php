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
class vendi_cache_test_base extends \WP_UnitTestCase
{
    //This is name of our FS root for testing
    private $_test_root_name = 'vendi-cache-test';

    //This is an instance of the Virtual File System
    private $_root;

    private $_logs = [];

    public function assertSameLastMessage($expected, $do_not_purge_logs = false)
    {
        if (0===count($this->_logs)) {
            throw new \Exception('No last message received');
        }

        $last_message = end($this->_logs);

        if (!$do_not_purge_logs) {
            $this->_purge_logs();
        }

        $this->assertArrayHasKey('message', $last_message);

        $this->assertSame($expected, $last_message['message']);
    }

    public function _purge_logs()
    {
        $this->_logs = [];
    }

    public function _handle_logger(array $record)
    {
        $this->_logs[] = $record;
    }

    public function get_vfs_root()
    {
        return $this->_root;
    }

    public function get_root_dir_name_no_trailing_slash()
    {
        return $this->_test_root_name;
    }

    public function setUp()
    {
        $this->_root = vfsStream::setup(
                                        $this->get_root_dir_name_no_trailing_slash(),
                                        null,
                                        [
                                            'wp-content/'
                                        ]
                                    );
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

        $maestro->with_file_system(new file_system_for_tests($maestro, vfsStream::url($this->get_root_dir_name_no_trailing_slash())));

        if (! $secretary) {
            $secretary = new \Vendi\Cache\Tests\non_global_constant_secretary();

            //These two constants are assumed to exist always so we'll bake them
            //in. There's really no way for them to not exist in a WP context,
            //even if SHORTINIT is used.
            //
            //The call to vfsStream::url() will make a path that basically looks like:
            //vfs://vendi-cache-test/
            //WordPress requires the trailing slash on it, too.
            $secretary->set_constant('ABSPATH', vfsStream::url($this->get_root_dir_name_no_trailing_slash() . '/'));
            $secretary->set_constant('WP_CONTENT_DIR', vfsStream::url($this->get_root_dir_name_no_trailing_slash() . '/wp-content/'));
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
