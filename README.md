# Vendi Cache - 2.0

[![Build Status](https://travis-ci.org/vendi-advertising/vendi-cache-2.svg?branch=master)](https://travis-ci.org/vendi-advertising/vendi-cache-2)
[![codecov](https://codecov.io/gh/vendi-advertising/vendi-cache-2/branch/master/graph/badge.svg)](https://codecov.io/gh/vendi-advertising/vendi-cache-2)

A complete rewrite of Vendi Cache (formerly Wordfence Falcon Cache). The goal of this rewrite is to hopefully replace the existing Vendi Cache 1.0 with a feature-matched version that has been rewritten from scratch, includes way more unit tests and can better handle PHP warning and errors that the previous automatically disabled caching for.

This version will have a minimum PHP version of probably 5.5. Because of this we are considering releasing this plugin as a second plugin instead of an upgrade of Vendi Cache 1.0. We are hoping to get feedback from users and we'll be monitoring [WordPress's plan on supporting required minimum versions of PHP](https://make.wordpress.org/plugins/2017/08/29/minimum-php-version-requirement/).

## New Features
 * [PSR-3 Logging](http://www.php-fig.org/psr/psr-3/) via [Monolog](https://github.com/Seldaek/monolog)
 * All parts of the HTTP request interpreted through [Symfony HttpFoundation](https://github.com/symfony/http-foundation)
 * Better file system abstraction via [Flysystem](http://flysystem.thephpleague.com/) and [File Path Utility](https://github.com/webmozart/path-util)
 * Unit Tests! Unit Tests! Unit Tests! Unit Tests!

## Goals
 * Offload code to _well-tested_ third-party libraries via composer
 * Log as much as possible for debugging
 * Only disable caching if a PHP warning/error surfaces a message
 * Explicit MU support
 * Allow caching of folders _without_ trailing spaces
 * Probably remove admin ajax library in order to simplify things
 * Use same filters, actions and constants from Vendi Cache 1.0 and Falcon Cache
 * WP-CLI support including `wp cache flush`

## Non-goals
 * Use a [PSR-6](http://www.php-fig.org/psr/psr-6/) or [PSR-16](http://www.php-fig.org/psr/psr-16/) logger
   * The entire point of this plugin is to cache files to disk so at this time I don't see a need to implement one of the common caching interfaces. I worry if I do that I'll get _too_ lost in abstraction and be also promising features that I can't test. Further, this plugin is meant to be used side-by-side with Apache or Nginx rules which are file-based. Although someone could write a PHP router handler to run through Redis/Memcache/whatever we'd still be incurring a PHP hit which we want to avoid.

## API
Below are the publically available constants, hooks and methods that are explicitly supported. Invoking any other code or disrupting any internal patterns could break things. If there's something missing from the API contract please [submit an issue](https://github.com/vendi-advertising/vendi-cache-2/issues).

### Constants
These constants may be defined outside of the plugin (for instance in `wp-config.php`) although it is recommended that you don't unless you have a very specific need. Certain constants require specific file system privileges and it is up to you to handle those if you override them.

#### `VENDI_CACHE_FOLDER_ABS`
String. Absolute path to the cache folder. Default is `WP_CONTENT_DIR/VENDI_CACHE_FOLDER_NAME`.

**WARNING:** If you change this you will need to manually update your Apache/Nginx rules.

#### `VENDI_CACHE_FOLDER_NAME`
String. If `VENDI_CACHE_FOLDER_ABS` is **not** defined then this is the folder relative to `WP_CONTENT_DIR` to use. Default is `vendi_cache`.

**WARNING:** If you change this you will need to manually update your Apache/Nginx rules.

#### `VENDI_CACHE_LOG_LEVEL`
Int. The level of logging to output. These values are defined in `Monolog/Logger.php` however their named values won't be available in `wp-config` (since the plugin won't have loaded yet) so you'll probably need to use their literal integer values.

**WARNING:** Setting a log level of `DEBUG` **will** cause a lot of noise to be written to the log file and should only be turned on for a very short amount of time.
 * `100` === `\Monolog\Logger::DEBUG`
 * `200` === `\Monolog\Logger::INFO`
 * `250` === `\Monolog\Logger::NOTICE`
 * `300` === `\Monolog\Logger::WARNING`
 * `400` === `\Monolog\Logger::ERROR`
 * `500` === `\Monolog\Logger::CRITICAL`
 * `550` === `\Monolog\Logger::ALERT`
 * `600` === `\Monolog\Logger::EMERGENCY`

#### `VENDI_CACHE_LOG_FILE_ABS`
String. The absolute path to the log file. Default is `VENDI_CACHE_FOLDER_ABS/vendi_cache.log`. **For security reasons you are encouraged to override this**.

### `VENDI_CACHE_PHP_ERROR`
Any. Setting this constant will overrise any hooks or filters. This should only ever be used if all other attempts at disabling the cache per request are not working.

### Hooks
The 2.0 release of this plugin adopts the `company/plugin/hook` pattern, so all hooks are prefaced by `vendi/cache/`.

#### Filters
##### `vendi/cache/do_not_cache_request`
If true, do not cache request. Default false.

#### Actions
##### `vendi/cache/clear`
TODO
