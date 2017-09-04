<?php

//You may define this elsewhere if you want to store the cache in a completely
//different folder, otherwise it will be stored relative to wp-content.
if( ! defined( 'VENDI_CACHE_FOLDER_ABS' ) )
{
    //You may define this elsewhere if you want to change the folder name
    //relative to wp-content.
    if( ! defined( 'VENDI_CACHE_FOLDER_NAME' ) )
    {
        define( 'VENDI_CACHE_FOLDER_NAME', 'vendi_cache');
    }

    define( 'VENDI_CACHE_FOLDER_ABS', \Webmozart\PathUtil\Path::join( WP_CONTENT_DIR, VENDI_CACHE_FOLDER_NAME ) );
}

/**
 * false
 * \Monolog\Logger::DEBUG
 * \Monolog\Logger::INFO
 * \Monolog\Logger::NOTICE
 * \Monolog\Logger::WARNING
 * \Monolog\Logger::ERROR
 * \Monolog\Logger::CRITICAL
 */
if( ! defined( 'VENDI_CACHE_LOG_LEVEL' ) )
{
    define( 'VENDI_CACHE_LOG_LEVEL', \Monolog\Logger::DEBUG );
}

//You are encouraged to define this elsewhere to secure the log file
if( ! defined( 'VENDI_CACHE_LOG_FILE_ABS' ) )
{
    define( 'VENDI_CACHE_LOG_FILE_ABS', \Webmozart\PathUtil\Path::join( VENDI_CACHE_FOLDER_ABS, 'vendi_cache.log' ) );
}
