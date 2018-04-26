<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Vendi\Cache\VendiPsr7RequestMaker;

class test_VendiPsr7RequestMaker extends vendi_cache_test_base_no_wordpress
{
    /**
     * @covers \Vendi\Cache\VendiPsr7RequestMaker::create_default_request
     */
    public function test_create_default_request()
    {
        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', VendiPsr7RequestMaker::create_default_request());
    }
}
