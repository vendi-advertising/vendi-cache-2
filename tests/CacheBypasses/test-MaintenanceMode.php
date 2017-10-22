<?php declare(strict_types=1);
namespace Vendi\Cache\Tests\CacheBypasses;

use Vendi\Cache\CacheBypasses\MaintenanceMode;
use Vendi\Cache\Tests\cache_bypass_base;
use Webmozart\PathUtil\Path;

class test_MaintenanceMode extends cache_bypass_base
{
    /**
     * @covers \Vendi\Cache\CacheBypasses\MaintenanceMode::is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable__magic_file()
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_secretary();

        //Create a temporary folder to simulate ABSPATH and set global constant
        $dir = $this->create_temp_dir();
        $cache_settings->set_constant('ABSPATH', $dir);

        $test = new MaintenanceMode($maestro);

        //Magic WP file path
        $path = Path::join($dir, '.maintenance');

        //Create the maintenance file
        $this->touch_file($path);

        //Make sure it exists
        $this->assertFileExists($path);

        //Caching should be disabled
        $this->assertTrue($test->is_resource_not_cacheable());
    }

    /**
     * @covers \Vendi\Cache\CacheBypasses\MaintenanceMode::is_resource_not_cacheable
     */
    public function test_is_resource_not_cacheable__filters()
    {
        //Common bootstrap
        $maestro = $this->__get_new_maestro();
        $cache_settings = $maestro->get_secretary();

        $test = new MaintenanceMode($maestro);

        //Create a temporary folder to simulate ABSPATH and set global constant
        $dir = $this->create_temp_dir();
        $cache_settings->set_constant('ABSPATH', $dir);

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
