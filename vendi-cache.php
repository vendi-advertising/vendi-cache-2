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

require_once VENDI_CACHE_DIR . '/includes/autoload.php';
require_once VENDI_CACHE_DIR . '/includes/constants.php';


// $url = 'https://www.vendiadvertising.com/work/';

// $html = wp_remote_get( $url );

// $file = \Vendi\Cache\CacheKeyGenerator::local_cache_filename_from_url( $url );

// $adapter = new \League\Flysystem\Adapter\Local(
//                                                 VENDI_CACHE_FOLDER_ABS,
//                                                 LOCK_EX,
//                                                 \League\Flysystem\Adapter\Local::DISALLOW_LINKS,
//                                                 [
//                                                     'file' => [
//                                                         'public'  => 0664,
//                                                         'private' => 0664,
//                                                     ],
//                                                     'dir' => [
//                                                         'public'  => 0755,
//                                                         'private' => 0755,
//                                                     ]
//                                                 ]
//                                             );
// $filesystem = new \League\Flysystem\Filesystem( $adapter );

// dump( $filesystem->has( $file ) );

// $filesystem->write( $file, $html[ 'body' ] );

// // $cache->set( fix_tag( $url ), $html[ 'body' ] );


// dump(  'https://www.vendiadvertising.com/' ) );
