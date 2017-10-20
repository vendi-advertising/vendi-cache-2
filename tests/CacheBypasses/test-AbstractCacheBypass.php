<?php

namespace Vendi\Cache\Tests\CacheBypasses;

use Monolog\Handler\NullHandler;
use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\CacheBypasses\AbstractCacheBypass;
use Vendi\Cache\Secretary;
use Vendi\Cache\Maestro;
use Vendi\Cache\Tests\vendi_cache_test_base;

class test_1 extends AbstractCacheBypass
{
    public function is_resource_not_cacheable( )
    {
        return false;
    }
}

class abstractcachebypass_for_test_log_request_as_not_cacheable extends AbstractCacheBypass
{
    public $search_text;

    public function is_resource_not_cacheable( )
    {
        //Invoke the logger which will be asserted above
        $this->log_request_as_not_cacheable(
                                                [
                                                    'reason' => $this->search_text,
                                                ]
         );
    }
}

class test_AbstractCacheBypass extends vendi_cache_test_base
{
    private $_url = 'http://www.example.com/cheese?a=b';

    private function _get_mock()
    {
        $maestro = ( new Maestro() )
                    ->with_secretary( new \Vendi\Cache\Tests\non_global_constant_secretary() )
                    ->with_request( Request::create( $this->_url ) )
                    ->with_logger(
                                    new \Monolog\Logger(
                                                    'vendi-cache-noop',
                                                    array( new NullHandler( ) )
                                                )
                     )
                ;

        return new test_1( $maestro );
    }

    /**
     * @covers Vendi\Cache\CacheBypasses\AbstractCacheBypass::__construct
     * @covers Vendi\Cache\CacheBypasses\AbstractCacheBypass::get_maestro
     * @covers Vendi\Cache\CacheBypasses\AbstractCacheBypass::get_secretary
     * @covers Vendi\Cache\CacheBypasses\AbstractCacheBypass::get_url
     * @covers Vendi\Cache\CacheBypasses\AbstractCacheBypass::get_query_string
     * @covers Vendi\Cache\CacheBypasses\AbstractCacheBypass::get_method
     * @covers Vendi\Cache\CacheBypasses\AbstractCacheBypass::get_path_url
     * @covers Vendi\Cache\CacheBypasses\AbstractCacheBypass::get_cookies
     */
    public function test_methods()
    {
        $mock = $this->_get_mock();

        $this->assertInstanceOf( 'Vendi\Cache\Maestro', $mock->get_maestro() );
        $this->assertInstanceOf( 'Vendi\Cache\Secretary', $mock->get_secretary() );
        $this->assertSame( $this->_url, $mock->get_url() );
        $this->assertSame( 'a=b', $mock->get_query_string() );
        $this->assertSame( 'GET', $mock->get_method() );
        $this->assertSame( '/cheese', $mock->get_path_url() );
        $this->assertInstanceOf( 'Symfony\Component\HttpFoundation\ParameterBag', $mock->get_cookies() );
    }

    /**
     * This is one ugly method to test just a single line, but that line is pretty important.
     *
     * @covers Vendi\Cache\CacheBypasses\AbstractCacheBypass::log_request_as_not_cacheable
     */
    public function test_log_request_as_not_cacheable()
    {
        $search_text = 'cheese';

        //Create a custom MonoLog handler based off of the simple NullHandler.
        //This handler will be given the string above to search for as well as
        //an instance of this class so that it can invoke PHP unit asserts.
        //Re-read the second part of that sentence again to make sure you
        //understand it.
        $handler = new nullhandler_log_handler(
                                                function( array $record ) use ( $search_text )
                                                {
                                                    $this->_handle( $record, $search_text );
                                                }
                                            );

        //Create a new Maestro with mostly default except for our customer
        //handler from above
        $maestro = ( new Maestro() )

                    ->with_logger(
                                    new \Monolog\Logger(
                                                    'vendi-cache-noop',
                                                    array( $handler )
                                                )
                     )
                ;

        //Finally, subclass our abstract class and create an instance
        $tester = new abstractcachebypass_for_test_log_request_as_not_cacheable( $maestro );

        //Set a public property (we can't change the constructor for
        //AbstractCacheBypass because it is final)
        $tester->search_text = $search_text;

        //Actually invoke the test (results don't matter) which invokes the
        //logger
        $tester->is_resource_not_cacheable();

    }

    private function _handle( array $record, $search_text )
    {
        //We're calling MonoLog's logger just once and it should match
        //everything that we expect

        //Check for known keys
        $this->assertArrayHasKey( 'message',    $record );
        $this->assertArrayHasKey( 'level_name', $record );
        $this->assertArrayHasKey( 'level',      $record );
        $this->assertArrayHasKey( 'channel',    $record );
        $this->assertArrayHasKey( 'context',    $record );

        //Check their values
        $this->assertSame( $record[ 'message'    ], 'Request not cacheable' );
        $this->assertSame( $record[ 'level_name' ], 'DEBUG' );
        $this->assertSame( $record[ 'level'      ], 100 );
        $this->assertSame( $record[ 'channel'    ], 'vendi-cache-noop' );

        //The context us the special part and it should be an array with one key
        //name "reason" and our specified value
        $this->assertTrue( is_array( $record[ 'context' ] ) );
        $this->assertCount( 1, $record[ 'context' ] );

        $this->assertArrayHasKey( 'reason',    $record[ 'context' ] );
        $this->assertSame( $record[ 'context' ][ 'reason' ], $search_text );


    }
}
