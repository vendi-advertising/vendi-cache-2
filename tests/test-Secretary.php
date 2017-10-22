<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use Psr\Log\LogLevel;
use Vendi\Cache\Maestro;
use Vendi\Cache\Secretary;
use Webmozart\PathUtil\Path;

/**
 * @group Secretary
 */
class test_Secretary extends vendi_cache_test_base
{
    /**
     * @covers \Vendi\Cache\Secretary::get_network_option
     * @covers \Vendi\Cache\Secretary::set_network_option
     */
    public function test_network_option()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();

        $this->assertFalse($secretary->get_network_option('CHEESE'));
        $secretary->set_network_option('CHEESE', 'GLORP');
        $this->assertSame('GLORP', $secretary->get_network_option('CHEESE'));
    }

    /**
     * @covers \Vendi\Cache\Secretary::is_constant_defined
     * @covers \Vendi\Cache\Secretary::get_constant_value
     */
    public function test_constants()
    {
        //We're testing actual constants here so we need the default Secretary
        $maestro = $this->__get_new_maestro(null, null, null, Maestro::get_default_secretary());
        $secretary = $maestro->get_secretary();

        $this->assertFalse($secretary->is_constant_defined('CHEESE'));
        define('CHEESE', 'GLORP');
        $this->assertTrue($secretary->is_constant_defined('CHEESE'));
        $this->assertSame('GLORP', $secretary->get_constant_value('CHEESE'));
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_cache_folder_abs
     */
    public function test_get_cache_folder_abs__VENDI_CACHE_FOLDER_ABS_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_FOLDER_ABS'));
        $secretary->set_constant('VENDI_CACHE_FOLDER_ABS', '/tmp');
        $this->assertSame('/tmp', $secretary->get_cache_folder_abs()) ;
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_cache_folder_abs
     */
    public function test_get_cache_folder_abs__VENDI_CACHE_FOLDER_NAME_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_FOLDER_ABS'));
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_FOLDER_NAME'));
        $secretary->set_constant('VENDI_CACHE_FOLDER_NAME', 'cheese');
        $this->assertSame(Path::join($secretary->get_constant_value('WP_CONTENT_DIR'), 'cheese'), $secretary->get_cache_folder_abs());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_cache_folder_abs
     */
    public function test_get_cache_folder_abs__no_constants_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_FOLDER_ABS'));
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_FOLDER_NAME'));
        $this->assertSame(Path::join($secretary->get_constant_value('WP_CONTENT_DIR'), 'vendi_cache'), $secretary->get_cache_folder_abs());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_log_file_abs
     */
    public function test_get_log_file_abs__VENDI_CACHE_LOG_FILE_ABS_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();

        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS'));
        $secretary->set_constant('VENDI_CACHE_LOG_FILE_ABS', '/tmp/cheese.log');
        $this->assertSame('/tmp/cheese.log', $secretary->get_log_file_abs()) ;
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_log_file_abs
     */
    public function test_get_log_file_abs__no_constant_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();

        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS'));
        $this->assertSame(Path::join($secretary->get_constant_value('WP_CONTENT_DIR'), 'vendi_cache', '__log__', 'vendi_cache.log'), $secretary->get_log_file_abs());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_log_folder_abs
     */
    public function test_get_log_folder_abs__VENDI_CACHE_LOG_FILE_ABS_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS'));
        $secretary->set_constant('VENDI_CACHE_LOG_FILE_ABS', '/tmp/cheese.log');
        $this->assertSame('/tmp', $secretary->get_log_folder_abs());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_log_folder_abs
     */
    public function test_get_log_folder_abs__VENDI_CACHE_LOG_FOLDER_ABS_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS'));
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FOLDER_ABS'));
        $secretary->set_constant('VENDI_CACHE_LOG_FOLDER_ABS', '/tmp');
        $this->assertSame('/tmp', $secretary->get_log_folder_abs());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_log_folder_abs
     */
    public function test_get_log_folder_abs__no_constant_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS'));
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FOLDER_ABS'));
        $this->assertSame(Path::join($secretary->get_constant_value('WP_CONTENT_DIR'), 'vendi_cache', '__log__'), $secretary->get_log_folder_abs());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_log_file_name
     */
    public function test_get_log_file_name__VENDI_CACHE_LOG_FILE_ABS_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS'));
        $secretary->set_constant('VENDI_CACHE_LOG_FILE_ABS', '/tmp/cheese.log');
        $this->assertSame('cheese.log', $secretary->get_log_file_name());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_log_file_name
     */
    public function test_get_log_file_name__VENDI_CACHE_LOG_FILE_NAME_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS'));
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FILE_NAME'));
        $secretary->set_constant('VENDI_CACHE_LOG_FILE_NAME', 'cheese.log');
        $this->assertSame('cheese.log', $secretary->get_log_file_name());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_log_file_name
     */
    public function test_get_log_file_name__no_constant_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS'));
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOG_FILE_NAME'));
        $this->assertSame('vendi_cache.log', $secretary->get_log_file_name());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_min_page_size
     * @covers \Vendi\Cache\Secretary::get_max_file_age
     * @covers \Vendi\Cache\Secretary::get_fs_permission_for_log_file
     * @covers \Vendi\Cache\Secretary::get_fs_permission_for_log_dir
     * @covers \Vendi\Cache\Secretary::get_fs_permission_for_cache_file_public
     * @covers \Vendi\Cache\Secretary::get_fs_permission_for_cache_dir_public
     * @covers \Vendi\Cache\Secretary::get_fs_permission_for_cache_file_private
     * @covers \Vendi\Cache\Secretary::get_fs_permission_for_cache_dir_private
     * @dataProvider provider_for_get_XYZ_CONSTANT_or_default_int
     * @param mixed $func
     * @param mixed $constant
     * @param mixed $default
     */
    public function test_get_XYZ_CONSTANT_or_default_int($func, $constant, $default)
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined($constant));
        $this->assertSame($default, $secretary->$func());

        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined($constant));
        $secretary->set_constant($constant, 10);
        $this->assertSame(10, $secretary->$func());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_logging_level
     */
    public function test_get_logging_level__VENDI_CACHE_LOGGING_LEVEL_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOGGING_LEVEL'));
        $secretary->set_constant('VENDI_CACHE_LOGGING_LEVEL', 10);
        $this->assertSame(10, $secretary->get_logging_level());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_logging_level
     */
    public function test_get_logging_level__no_constant_set()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertFalse($secretary->is_constant_defined('VENDI_CACHE_LOGGING_LEVEL'));
        $this->assertSame(LogLevel::DEBUG, $secretary->get_logging_level());
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_option_value
     * @dataProvider provider_for_get_named_option
     * @param mixed $name
     * @param mixed $default
     * @param mixed $value_to_test
     */
    public function test_get_option_value__default($name, $default, $value_to_test)
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $option = $secretary->get_named_option($name);
        $this->assertInstanceOf("\\Vendi\\Cache\\CacheOptions\\$name", $option);
        $this->assertSame($default, $secretary->get_option_value($option));
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_option_value
     * @dataProvider provider_for_get_named_option
     * @param mixed $name
     * @param mixed $default
     * @param mixed $value_to_test
     */
    public function test_get_option_value($name, $default, $value_to_test)
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $option = $secretary->get_named_option($name);
        $this->assertInstanceOf("\\Vendi\\Cache\\CacheOptions\\$name", $option);
        update_option($option->get_storage_name(), $value_to_test);
        $this->assertSame($value_to_test, $secretary->get_option_value($option));
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_option_value
     * @dataProvider provider_for_get_named_option
     * @param mixed $name
     * @param mixed $default
     * @param mixed $value_to_test
     */
    public function test_get_option_value__invalid($name, $default, $value_to_test)
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $option = $secretary->get_named_option($name);
        $this->assertInstanceOf("\\Vendi\\Cache\\CacheOptions\\$name", $option);

        $invalid_value = 'CHEESE';
        $storage_name = $option->get_storage_name();
        update_option($storage_name, $invalid_value);

        $this->setExpectedException('\Exception', "Unsupported cache value for $storage_name: $invalid_value");
        $secretary->get_option_value($option);
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_named_option
     * @dataProvider provider_for_get_named_option
     * @param mixed $name
     * @param mixed $default
     * @param mixed $value_to_test
     */
    public function test_get_named_option($name, $default, $value_to_test)
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->assertInstanceOf("\\Vendi\\Cache\\CacheOptions\\$name", $secretary->get_named_option($name));
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_named_option
     */
    public function test_get_named_option__invalid()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $this->setExpectedException('\Exception', 'Unknown cache option: cheese');
        $secretary->get_named_option('cheese');
    }

    /**
     * @covers \Vendi\Cache\Secretary::get_fs_permissions_for_cache
     */
    public function test_get_fs_permissions_for_cache()
    {
        $secretary = $this->__get_new_maestro()->get_secretary();
        $expected = [
                        'file' =>
                                    [
                                        'public'  => 0664,
                                        'private' => 0664,
                                    ],
                        'dir' =>
                                    [
                                        'public'  => 0777,
                                        'private' => 0777,
                                    ]
                    ];
        $this->assertEqualSets($expected, $secretary->get_fs_permissions_for_cache());
    }

    public function provider_for_get_XYZ_CONSTANT_or_default_int()
    {
        return [
                    [ 'get_min_page_size', 'VENDI_CACHE_MIN_PAGE_SIZE', 1000 ],
                    [ 'get_max_file_age', 'VENDI_CACHE_MAX_FILE_AGE', 10000 ],
                    [ 'get_fs_permission_for_log_file', 'VENDI_CACHE_FS_PERM_LOG_FILE', 0664 ],
                    [ 'get_fs_permission_for_log_dir', 'VENDI_CACHE_FS_PERM_LOG_DIR', 0775 ],

                    [ 'get_fs_permission_for_cache_file_public', 'VENDI_CACHE_FS_PERM_FILE_PUBLIC', 0664 ],
                    [ 'get_fs_permission_for_cache_dir_public', 'VENDI_CACHE_FS_PERM_DIR_PUBLIC', 0777 ],
                    [ 'get_fs_permission_for_cache_file_private', 'VENDI_CACHE_FS_PERM_FILE_PRIVATE', 0664 ],
                    [ 'get_fs_permission_for_cache_dir_private', 'VENDI_CACHE_FS_PERM_DIR_PRIVATE', 0777 ],
        ];
    }

    public function provider_for_get_named_option()
    {
        return [
                    [ 'CacheMode',    'off', 'on'  ],
                    [ 'DebugLogging', 'off', 'off' ],
                    [ 'DebugComment', 'on',  'off' ],
        ];
    }
}
