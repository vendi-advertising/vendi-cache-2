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
define('VENDI_CACHE_FILE', __FILE__);
define('VENDI_CACHE_DIR', dirname(__FILE__));
define('VENDI_CACHE_URL', plugin_dir_url(__FILE__));

define('VENDI_CACHE_VERSION', '2.0.0');

require_once VENDI_CACHE_DIR . '/includes/autoload.php';

require_once VENDI_CACHE_DIR . '/includes/hooks.php';

if (defined('WP_CLI')) {
    require_once VENDI_CACHE_DIR . '/cli/Vendi_Cache_Command.php';

    WP_CLI::add_command('vendi-cache', 'Vendi_Cache_Command');
}
