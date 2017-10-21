<?php declare(strict_types=1);
namespace Vendi\Cache\CacheBypasses;

use Webmozart\PathUtil\Path;

final class MaintenanceMode extends AbstractCacheBypass
{
    public function is_resource_not_cacheable()
    {
        $settings = $this->get_secretary();

        $abs = $settings->get_constant_value('ABSPATH');
        $test_file = Path::join($abs, '.maintenance');

        if (file_exists($test_file)) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Maintenance mode',
                                                        'src'    => 'File exists',
                                                        'file'   => $test_file,
                                                    ]
                                            );
            return true;
        }

        if (apply_filters('enable_maintenance_mode', false, 0)) {
            $this->log_request_as_not_cacheable(
                                                    [
                                                        'reason' => 'Maintenance mode',
                                                        'src'    => 'Filter enable_maintenance_mode returned true',
                                                    ]
                                            );
            return true;
        }

        return false;
    }
}
