<?php declare(strict_types=1);
namespace Vendi\Cache\CacheBypasses;

use Vendi\Cache\Maestro;

final class WpRepairing extends AbstractCacheBypassWithConstant
{
    public function __construct(Maestro $maestro)
    {
        parent::__construct($maestro, 'WP_REPAIRING');
    }
}
