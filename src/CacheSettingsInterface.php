<?php

namespace Vendi\Cache;

interface CacheSettingsInterface
{

    public function is_constant_defined( $name );

    public function get_constant_value( $name );

    public function is_function_defined( $name );

    public function get_function_value( $name );

    public function get_cache_folder_abs();

    /**
     * The absolute path to the log file.
     *
     * @return string
     */
    public function get_log_file_abs();

    public function get_log_folder_abs();

    public function get_log_file_name();

    /**
     * The maximum age in seconds that a file should exist in the cache.
     * @return int
     */
    public function get_max_file_age();

    /**
     * The minimum byte size for a request to cache.
     * @return int
     */
    public function get_min_page_size();

    public function get_fs_permissions_for_cache();

    public function get_fs_permission_for_log_file();

    public function get_fs_permission_for_log_dir();

    public function get_logging_level();

    public function get_is_auditing_enabled();
}
