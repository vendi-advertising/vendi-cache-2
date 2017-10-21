<?php declare(strict_types=1);
namespace Vendi\Cache;

use GuzzleHttp\Psr7\ServerRequest;

final class VendiPsr7RequestMaker
{
    final public static function create_default_request()
    {
        return ServerRequest::fromGlobals();
    }
}
