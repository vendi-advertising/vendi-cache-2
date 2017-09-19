<?php

namespace Vendi\Cache\CacheBypasses;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

interface CacheBypassInterface
{
    public function __construct( Request $request, LoggerInterface $logger );

    public function get_url( );

    public function get_query_string( );

    public function get_method( );

    public function get_path_url( );

    public function is_cacheable( );

    public function get_cookies( );
}
