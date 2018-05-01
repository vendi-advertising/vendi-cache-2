<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use org\bovigo\vfs\vfsStream;
use Vendi\Cache\VendiPsr3Logger;

/**
 * @group Logger
 */
class test_VendiPsr3Logger extends vendi_cache_test_base_no_wordpress
{
    /**
     * @covers \Vendi\Cache\VendiPsr3Logger::get_request_id
     * @covers \Vendi\Cache\VendiPsr3Logger::_generate_new_request_id
     */
    public function test_get_request_id()
    {
        $logger = new VendiPsr3Logger();
        $this->assertNull($logger->get_request_id());
        $logger->_generate_new_request_id();
        $this->assertNotNull($logger->get_request_id());
    }

    /**
     * @covers \Vendi\Cache\VendiPsr3Logger::__construct
     */
    public function test___construct()
    {
        $logger = new VendiPsr3Logger();
        $this->assertNull($logger->get_request_id());

        $logger = new VendiPsr3Logger($this->__get_new_maestro()->get_secretary());
        $this->assertNotNull($logger->get_request_id());
    }

    /**
     * @covers \Vendi\Cache\VendiPsr3Logger::_append_request_id_to_logger
     */
    public function test__append_request_id_to_logger()
    {
        $maestro = $this->__get_new_maestro();
        $secretary = $maestro->get_secretary();

        $logger = new VendiPsr3Logger();
        $logger->_generate_new_request_id();
        $this->assertNotNull($logger->get_request_id());
        $this->assertSame(
                            ['context'=>['request_id' => $logger->get_request_id() ]],
                            $logger->_append_request_id_to_logger(['context'=>[]])
                        );
    }

    /**
     * @covers \Vendi\Cache\VendiPsr3Logger::_maybe_create_log_dir
     */
    public function test__maybe_create_log_dir()
    {
        //Required to init VFS root which is lazy-loaded
        $this->get_vfs_root();

        $maestro = $this->__get_new_maestro();
        $secretary = $maestro->get_secretary();

        $logger = new VendiPsr3Logger();
        $this->assertFileNotExists(\dirname($secretary->get_log_file_abs()));
        $logger->_maybe_create_log_dir($maestro->get_secretary());
        $this->assertFileExists(\dirname($secretary->get_log_file_abs()));
    }

    public function test__init()
    {
        //The ABS path to our test log
        $abs = vfsStream::url($this->get_root_dir_name_no_trailing_slash() . '/cheese/cheese.log');

        //We don't want the default maestro here because we want to write to an
        //actual file for testing. Also, creating the maestro automatically logs
        //stuff which we don't want
        $secretary = new non_global_constant_secretary();
        $secretary->set_constant('VENDI_CACHE_LOG_FILE_ABS', $abs);
        $logger = new VendiPsr3Logger();

        $this->assertFileNotExists(\dirname($secretary->get_log_file_abs()));
        $this->assertNull($logger->get_request_id());

        $logger->_init($secretary);

        $this->assertNotNull($logger->get_request_id());
        $this->assertFileExists(\dirname($secretary->get_log_file_abs()));
    }

    /**
     * @covers \Vendi\Cache\VendiPsr3Logger::_maybe_create_log_dir
     *
     * This test creates a folder structure that the logger doesn't have
     * permission to create things in.
     */
    public function test__maybe_create_log_dir__exception()
    {
        $the_first_folder = 'alpha';
        $the_second_folder = 'beta';

        //The ABS path to our test log
        $abs = vfsStream::url($this->get_root_dir_name_no_trailing_slash() . "/$the_first_folder/$the_second_folder/cheese.log");

        //Create a directory with no permissions
        vfsStream::newDirectory($the_first_folder, 0000)
            ->at(
                    $this
                        ->get_vfs_root()
                )
        ;

        //We don't want the default maestro here because we want to write to an
        //actual file for testing. Also, creating the maestro automatically logs
        //stuff which we don't want
        $secretary = new non_global_constant_secretary();
        $secretary->set_constant('VENDI_CACHE_LOG_FILE_ABS', $abs);

        $this->setExpectedException('\Exception', 'Could not create directory for logging');
        $logger = new VendiPsr3Logger($secretary);
    }

    /**
     * @covers \Vendi\Cache\VendiPsr3Logger::_create_and_push_stream_handler
     */
    public function test___create_and_push_stream_handler()
    {
        //The ABS path to our test log
        $abs = vfsStream::url($this->get_root_dir_name_no_trailing_slash() . '/cheese.log');

        //We don't want the default maestro here because we want to write to an
        //actual file for testing. Also, creating the maestro automatically logs
        //stuff which we don't want
        $secretary = new non_global_constant_secretary();
        $secretary->set_constant('VENDI_CACHE_LOG_FILE_ABS', $abs);

        $logger = new VendiPsr3Logger();
        $logger->_maybe_create_log_dir($secretary);
        $logger->_create_and_push_stream_handler($secretary);

        //We haven't logged anything yet so this file shouldn't exist
        $this->assertFileNotExists($abs);

        //Log something
        $logger->debug('cheese');

        $this->assertFileExists($abs);

        //We should now have a single line in the format:
        //[DATE TIME] [] [DEBUG] [CHEESE]
        //Break into chunks at the whitespace, note the whitespace between
        //DATE and TIME
        $text_parts = \explode(' ', \trim(\file_get_contents($abs)));
        $this->assertCount(5, $text_parts);

        //Don't test DATE and TIME
        \array_shift($text_parts);
        \array_shift($text_parts);

        //These should be fixed values
        $this->assertSame('[]', \array_shift($text_parts));
        $this->assertSame('[DEBUG]:', \array_shift($text_parts));
        $this->assertSame('cheese', \array_shift($text_parts));
    }
}
