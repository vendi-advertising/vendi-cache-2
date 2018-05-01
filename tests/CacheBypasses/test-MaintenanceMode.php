<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheBypasses;

use org\bovigo\vfs\vfsStream;
use Vendi\Cache\CacheBypasses\MaintenanceMode;
use Vendi\Cache\Tests\cache_bypass_base;

class test_MaintenanceMode extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\MaintenanceMode::is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable__magic_file()
    {
        //Required to init VFS root which is lazy-loaded
        $this->get_vfs_root();

        //Common bootstrap
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_secretary();

        $test = new MaintenanceMode($maestro);

        \touch(vfsStream::url($this->get_root_dir_name_no_trailing_slash() . '/.maintenance'));

        //Make sure it exists
        $this->assertFileExists(vfsStream::url($this->get_root_dir_name_no_trailing_slash() . '/.maintenance'));

        //Caching should be disabled
        $this->assertTrue($test->is_resource_not_cacheable());
    }

    /**
     * @covers \Vendi\Cache\CacheBypasses\MaintenanceMode::is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable__filters()
    {
        //Required to init VFS root which is lazy-loaded
        $this->get_vfs_root();

        //Common bootstrap
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_secretary();

        $test = new MaintenanceMode($maestro);

        $this->assertFalse($test->is_resource_not_cacheable());

        remove_all_filters('enable_maintenance_mode');
        add_filter('enable_maintenance_mode', function () {
            return true;
        });
        $this->assertTrue($test->is_resource_not_cacheable());

        remove_all_filters('enable_maintenance_mode');
        add_filter('enable_maintenance_mode', function () {
            return false;
        });
        $this->assertFalse($test->is_resource_not_cacheable());

        remove_all_filters('enable_maintenance_mode');

        $this->assertFalse($test->is_resource_not_cacheable());
    }
}
