<?php

namespace Vendi\Cache;

use Assert\Assertion;

class DefaultSettings implements CacheSettingsInterface
{
    private static $_log_folder_name = '__log__';

    private $_constant_helper;

    public function is_constant_defined( $name )
    {
        return defined( $name );
    }

    public function get_constant_value( $name )
    {
        Assertion::defined( $name );
        return constant( $name );
    }

    public function is_function_defined( $name )
    {
        return function_exists( $name );
    }

    public function get_function_value( $name )
    {
        Assertion::isCallable( $name );

        $args = func_get_args();

        //First $args is actually the $name variable above
        switch( count( $args ) )
        {
            case 1:
                return $name();

            case 2:
                return $name( $args[ 1 ] );

            case 3:
                return $name( $args[ 1 ], $args[ 2 ] );

            case 4:
                return $name( $args[ 1 ], $args[ 2 ], $args[ 3 ] );

            case 5:
                return $name( $args[ 1 ], $args[ 2 ], $args[ 3 ], $args[ 4 ] );
        }

        throw new \Exception( 'Custom get_function_value() only support a maximum of 4 arguments' );
    }

    public function get_cache_folder_abs()
    {
        //If this is defined, use it directly
        if( $this->is_constant_defined( 'VENDI_CACHE_FOLDER_ABS' ) )
        {
            return $this->get_constant_value( 'VENDI_CACHE_FOLDER_ABS' );
        }

        //If this is defined, use it relative to wp-content
        if( $this->is_constant_defined( 'VENDI_CACHE_FOLDER_NAME' ) )
        {
            return \Webmozart\PathUtil\Path::join(
                                                    $this->get_constant_value( 'WP_CONTENT_DIR' ),
                                                    $this->get_constant_value( 'VENDI_CACHE_FOLDER_NAME' )
                                                );
        }

        //Default, return ABS path to wp-content/vendi_cache
        return \Webmozart\PathUtil\Path::join(
                                                $this->get_constant_value( 'WP_CONTENT_DIR' ),
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
        if( $this->is_constant_defined( 'VENDI_CACHE_LOG_FILE_ABS' ) )
        {
            return $this->get_constant_value( 'VENDI_CACHE_LOG_FILE_ABS' );
        }

        return \Webmozart\PathUtil\Path::join( $this->get_log_folder_abs(), $this->get_log_file_name() );
    }

    public function get_log_folder_abs()
    {
        //If the ABS for the file is provided then just return the parent
        //folder of that.
        if( $this->is_constant_defined( 'VENDI_CACHE_LOG_FILE_ABS' ) )
        {
            return dirname( $this->get_constant_value( 'VENDI_CACHE_LOG_FILE_ABS' ) );
        }

        if( $this->is_constant_defined( 'VENDI_CACHE_LOG_FOLDER_ABS' ) )
        {
            return $this->get_constant_value( 'VENDI_CACHE_LOG_FOLDER_ABS' );
        }

        return \Webmozart\PathUtil\Path::join( $this->get_cache_folder_abs(), self::$_log_folder_name );
    }

    public function get_log_file_name()
    {
        if( $this->is_constant_defined( 'VENDI_CACHE_LOG_FILE_ABS' ) )
        {
            return basename( $this->get_constant_value( 'VENDI_CACHE_LOG_FILE_ABS' ) );
        }

        if( $this->is_constant_defined( 'VENDI_CACHE_LOG_FILE_NAME' ) )
        {
            return $this->get_constant_value( 'VENDI_CACHE_LOG_FILE_NAME' );
        }

        return 'vendi_cache.log';
    }

    /**
     * The maximum age in seconds that a file should exist in the cache.
     * @return int
     */
    public function get_max_file_age()
    {
        if( $this->is_constant_defined( 'VENDI_CACHE_MAX_FILE_AGE' ) )
        {
            return (int)$this->get_constant_value( 'VENDI_CACHE_MAX_FILE_AGE' );
        }

        return 10000;
    }

    /**
     * The minimum byte size for a request to cache.
     * @return int
     */
    public function get_min_page_size()
    {
        if( $this->is_constant_defined( 'VENDI_CACHE_MIN_PAGE_SIZE' ) )
        {
            return (int)$this->get_constant_value( 'VENDI_CACHE_MIN_PAGE_SIZE' );
        }

        return 1000;
    }

    public function get_fs_permissions_for_cache()
    {
        return [
                'file' =>
                            [
                                'public'  => $this->is_constant_defined( 'VENDI_CACHE_FS_PERM_FILE_PUBLIC')  ? $this->get_constant_value( 'VENDI_CACHE_FS_PERM_FILE_PUBLIC' ) : 0664,
                                'private' => $this->is_constant_defined( 'VENDI_CACHE_FS_PERM_FILE_PRIVATE') ? $this->get_constant_value( 'VENDI_CACHE_FS_PERM_FILE_PRIVATE' ) : 0664,
                            ],
                'dir' =>
                            [
                                'public'  => $this->is_constant_defined( 'VENDI_CACHE_FS_PERM_DIR_PUBLIC')   ? $this->get_constant_value( 'VENDI_CACHE_FS_PERM_DIR_PUBLIC' )   : 0777,
                                'private' => $this->is_constant_defined( 'VENDI_CACHE_FS_PERM_DIR_PRIVATE')  ? $this->get_constant_value( 'VENDI_CACHE_FS_PERM_DIR_PRIVATE' )  : 0777,
                            ]
            ];
    }

    public function get_fs_permission_for_log_file()
    {
        return $this->is_constant_defined( 'VENDI_CACHE_FS_PERM_LOG_FILE') ? $this->get_constant_value( 'VENDI_CACHE_FS_PERM_LOG' ) : 0664;
    }

    public function get_fs_permission_for_log_dir()
    {
        return $this->is_constant_defined( 'VENDI_CACHE_FS_PERM_LOG_DIR') ? $this->get_constant_value( 'VENDI_CACHE_FS_PERM_LOG' ) : 0775;
    }

    public function get_logging_level()
    {
        if( $this->is_constant_defined( 'VENDI_CACHE_LOGGING_LEVEL' ) )
        {
            return (int)$this->get_constant_value( 'VENDI_CACHE_LOGGING_LEVEL' );
        }

        return \Monolog\Logger::DEBUG;
    }

    public function get_is_auditing_enabled()
    {
        return true;
    }
}
