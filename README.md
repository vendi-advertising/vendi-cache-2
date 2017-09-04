# Vendi Cache - 2.0
A complete rewrite of Vendi Cache (formerly Wordfence Falcon Cache). The goal of this rewrite is to hopefully replace the existing Vendi Cache 1.0 with a feature-matched version that has been rewritten from scratch, includes way more unit tests and can better handle PHP warning and errors that the previous automatically disabled caching for.

This version will have a minimum PHP version of probably 5.5. Because of this we are considering releasing this plugin as a second plugin instead of an upgrade of Vendi Cache 1.0. We are hoping to get feedback from users and we'll be monitoring [WordPress's plan on supporting required minimum versions of PHP](https://make.wordpress.org/plugins/2017/08/29/minimum-php-version-requirement/).

## New Features
 * Built-in logging via [Monolog](https://github.com/Seldaek/monolog)
 * Better file system abstraction via [Flysystem](http://flysystem.thephpleague.com/) and [File Path Utility](https://github.com/webmozart/path-util)
 * Unit Tests! Unit Tests! Unit Tests! Unit Tests!

## Goals
 * Offload code to third-party libraries via composer
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

