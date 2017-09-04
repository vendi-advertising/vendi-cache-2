<?php
/*
Plugin Name: Vendi Cache 2.0
Description: Disk-based page and post cache. (Formerly Wordfence Falcon Cache)
Plugin URI: https://www.vendiadvertising.com/
Author: Vendi Advertising (Chris Haas)
Version: 2.0.0
Author URI: https://www.vendiadvertising.com/
Text Domain: vendi-cache
Domain Path: /languages
*/

//Shortcuts to the root of the plugin for various formats
define( 'VENDI_CACHE_FILE', __FILE__ );
define( 'VENDI_CACHE_DIR', dirname( __FILE__ ) );
define( 'VENDI_CACHE_URL', plugin_dir_url( __FILE__ ) );

define( 'VENDI_CACHE_VERSION', '2.0.0' );

require_once VENDI_CACHE_DIR . '/vendor/autoload.php';

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
