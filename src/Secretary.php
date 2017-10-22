<?php declare(strict_types=1);
namespace Vendi\Cache;

use Assert\Assertion;
use Psr\Log\LogLevel;
use Vendi\Cache\CacheOptions\CacheOptionInterface;

class Secretary
{
    private static $_log_folder_name = '__log__';

    private $_constant_helper;

    public function get_network_option($name)
    {
        Assertion::isCallable('\get_site_option');
        return get_site_option($name);
    }

    public function set_network_option($name, $value)
    {
        Assertion::isCallable('\update_site_option');
        update_site_option($name, $value);
    }

    public function is_constant_defined($name)
    {
        return defined($name);
    }

    public function get_constant_value($name)
    {
        Assertion::defined($name);
        return constant($name);
    }

    public function get_cache_folder_abs()
    {
        //If this is defined, use it directly
        if ($this->is_constant_defined('VENDI_CACHE_FOLDER_ABS')) {
            return $this->get_constant_value('VENDI_CACHE_FOLDER_ABS');
        }

        //If this is defined, use it relative to wp-content
        if ($this->is_constant_defined('VENDI_CACHE_FOLDER_NAME')) {
            return \Webmozart\PathUtil\Path::join(
                                                    $this->get_constant_value('WP_CONTENT_DIR'),
                                                    $this->get_constant_value('VENDI_CACHE_FOLDER_NAME')
                                                );
        }

        //Default, return ABS path to wp-content/vendi_cache
        return \Webmozart\PathUtil\Path::join(
                                                $this->get_constant_value('WP_CONTENT_DIR'),
                                                'vendi_cache'
                                            );
    }

    /**
     * The absolute path to the log file.
     *
     * @return string
     */
    public function get_log_file_abs()
    {
        if ($this->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS')) {
            return $this->get_constant_value('VENDI_CACHE_LOG_FILE_ABS');
        }

        return \Webmozart\PathUtil\Path::join($this->get_log_folder_abs(), $this->get_log_file_name());
    }

    public function get_log_folder_abs()
    {
        //If the ABS for the file is provided then just return the parent
        //folder of that.
        if ($this->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS')) {
            return dirname($this->get_constant_value('VENDI_CACHE_LOG_FILE_ABS'));
        }

        if ($this->is_constant_defined('VENDI_CACHE_LOG_FOLDER_ABS')) {
            return $this->get_constant_value('VENDI_CACHE_LOG_FOLDER_ABS');
        }

        return \Webmozart\PathUtil\Path::join($this->get_cache_folder_abs(), self::$_log_folder_name);
    }

    public function get_log_file_name()
    {
        if ($this->is_constant_defined('VENDI_CACHE_LOG_FILE_ABS')) {
            return basename($this->get_constant_value('VENDI_CACHE_LOG_FILE_ABS'));
        }

        if ($this->is_constant_defined('VENDI_CACHE_LOG_FILE_NAME')) {
            return $this->get_constant_value('VENDI_CACHE_LOG_FILE_NAME');
        }

        return 'vendi_cache.log';
    }

    /**
     * The maximum age in seconds that a file should exist in the cache.
     * @return int
     */
    public function get_max_file_age()
    {
        if ($this->is_constant_defined('VENDI_CACHE_MAX_FILE_AGE')) {
            return (int) $this->get_constant_value('VENDI_CACHE_MAX_FILE_AGE');
        }

        return 10000;
    }

    /**
     * The minimum byte size for a request to cache.
     * @return int
     */
    public function get_min_page_size()
    {
        if ($this->is_constant_defined('VENDI_CACHE_MIN_PAGE_SIZE')) {
            return (int) $this->get_constant_value('VENDI_CACHE_MIN_PAGE_SIZE');
        }

        return 1000;
    }

    public function get_fs_permission_for_log_file()
    {
        if ($this->is_constant_defined('VENDI_CACHE_FS_PERM_LOG_FILE')) {
            return (int) $this->get_constant_value('VENDI_CACHE_FS_PERM_LOG_FILE');
        }

        return 0664;
    }

    public function get_fs_permission_for_log_dir()
    {
        if ($this->is_constant_defined('VENDI_CACHE_FS_PERM_LOG_DIR')) {
            return (int) $this->get_constant_value('VENDI_CACHE_FS_PERM_LOG_DIR');
        }

        return 0775;
    }

    public function get_fs_permission_for_cache_file_public()
    {
        if ($this->is_constant_defined('VENDI_CACHE_FS_PERM_FILE_PUBLIC')) {
            return (int) $this->get_constant_value('VENDI_CACHE_FS_PERM_FILE_PUBLIC');
        }

        return 0664;
    }

    public function get_fs_permission_for_cache_dir_public()
    {
        if ($this->is_constant_defined('VENDI_CACHE_FS_PERM_DIR_PUBLIC')) {
            return (int) $this->get_constant_value('VENDI_CACHE_FS_PERM_DIR_PUBLIC');
        }

        return 0777;
    }

    public function get_fs_permission_for_cache_file_private()
    {
        if ($this->is_constant_defined('VENDI_CACHE_FS_PERM_FILE_PRIVATE')) {
            return (int) $this->get_constant_value('VENDI_CACHE_FS_PERM_FILE_PRIVATE');
        }

        return 0664;
    }

    public function get_fs_permission_for_cache_dir_private()
    {
        if ($this->is_constant_defined('VENDI_CACHE_FS_PERM_DIR_PRIVATE')) {
            return (int) $this->get_constant_value('VENDI_CACHE_FS_PERM_DIR_PRIVATE');
        }

        return 0777;
    }

    public function get_fs_permissions_for_cache()
    {
        return [
                'file' =>
                            [
                                'public'  => $this->get_fs_permission_for_cache_file_public(),
                                'private' => $this->get_fs_permission_for_cache_file_private(),
                            ],
                'dir' =>
                            [
                                'public'  => $this->get_fs_permission_for_cache_dir_public(),
                                'private' => $this->get_fs_permission_for_cache_dir_private(),
                            ]
            ];
    }

    /**
     * [get_logging_level description].
     * @return string A PSR-3 logging level
     */
    public function get_logging_level()
    {
        if ($this->is_constant_defined('VENDI_CACHE_LOGGING_LEVEL')) {
            return $this->get_constant_value('VENDI_CACHE_LOGGING_LEVEL');
        }

        return LogLevel::DEBUG;
    }

    public function get_option_value(CacheOptionInterface $option)
    {
        $value = get_option($option->get_storage_name(), false);

        if (false === $value) {
            return $option->get_default_value();
        }

        if (! $option->is_value_valid($value)) {
            $name = esc_html($option->get_storage_name());
            $value = esc_html($value);
            throw new \Exception("Unsupported cache value for $name: $value");
        }

        return $value;
    }

    public function get_named_option($name)
    {
        Assertion::notEmpty($name);
        Assertion::string($name);

        switch ($name) {
            case 'CacheMode':
            case 'DebugLogging':
            case 'DebugComment':
                $option = "\\Vendi\\Cache\\CacheOptions\\$name";
                return new $option($this);

            default:
                throw new \Exception('Unknown cache option: ' . esc_html($name));
        }
    }
}
