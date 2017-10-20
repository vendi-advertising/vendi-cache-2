<?php

namespace Vendi\Cache;

use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;

final class VendiPsr7RequestMaker
{
    final public static function create_default_request()
    {
        return self::convert_symfony_request(Request::createFromGlobals());
    }

    final public static function convert_symfony_request(Request $request)
    {
        return ( new DiactorosFactory() )
            ->createRequest($request)
        ;
    }
}
