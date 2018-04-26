<?php

declare(strict_types=1);

namespace Vendi\Cache\Tests;

use GuzzleHttp\Psr7\ServerRequest;
use org\bovigo\vfs\vfsStream;
use Vendi\Cache\Maestro;
use Vendi\Cache\Secretary;
use Webmozart\PathUtil\Path;

/**
 * @coversNothing
 */
interface vendi_cache_test_base_interface
{
    public function assertSameLastMessage($expected, $do_not_purge_logs = false);

    public function _purge_logs();

    public function _handle_logger(array $record);

    public function get_vfs_root();

    public function get_root_dir_name_no_trailing_slash();

    public function __create_server_request_with_custom_headers(array $headers = []);

    public function __create_server_request_from_url($url, $method = 'GET', array $cookies = []);

    public function __get_new_maestro(ServerRequest $request = null, callable $handle_function = null, Secretary $secretary = null);

    public function arrays_are_similar($a, $b);
}
