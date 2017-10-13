# Overview

## Maestro
The [`Maestro`](https://github.com/vendi-advertising/vendi-cache-2/blob/master/src/Maestro.php) class represents the
main entry for everything. All code should start with a instance of this class with optional overrides for `Request`,
`Logger` and `Secretary`.

Unless unit testing, the main entry should always be `\Vendi\Cache\Maestro::get_default_instance()`.

This class is called Maestro instead of Conductor because I didn't want to confuse anything with Composer.

## Secretary
The [`Secretary`](https://github.com/vendi-advertising/vendi-cache-2/blob/master/src/Secretary.php) class holds all
defaults for the plugins (such as log file name, folder permissions, etc.) as well as provides access to user-provided
settings such as caching mode.

Many things that disable caching for a given request rely on global PHP constants. This class provides helper methods
such as `is_constant_defined` which just invokes `\defined` and `is_function_defined` which just invokes
`\function_exists`. These methods exist in order to make unit testing easier due to problem with backing up globals that
hold closures.

The Secretary should always be access from a Maestro by calling `get_secretary()`.

## Request
The [`Request`](https://github.com/symfony/http-foundation/blob/master/Request.php) class from
[Symfony](https://symfony.com/) represents the currently requested resource. This is the only class that should ever
directly access the automatic global variables such as `$_SERVER`, `$_GET` and `$_POST`.

The Request should always be access from a Maestro by calling `get_request()`.

## Logger
The [`Logger`](https://github.com/php-fig/log/blob/master/Psr/Log/LoggerInterface.php) class is a
[PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) compatible Logger.

The default implementation is `VendiMonoLogger` which is a subclass of [Monolog](https://seldaek.github.io/monolog/)
[`Monolog\Logger`](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Logger.php). This subclass has a default
`StreamHandler` that writes to disk using a format that uniquely identifies individual requests.

Technically speaking, nothing actually requires Monolog and any other PS3-3 compatible logger should work just fine
however this has not been tested.

## CacheMaster
The [`CacheMaster`](https://github.com/vendi-advertising/vendi-cache-2/blob/master/src/CacheMaster.php) class represents
the main worker the process the request and potentially caches it for subsequent requests. It also handles cache purges.

This class was created before Maestro and I think I'm going to eventually rename it.
