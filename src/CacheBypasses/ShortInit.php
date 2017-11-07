<?php declare(strict_types=1);
namespace Vendi\Cache\CacheBypasses;

use Vendi\Cache\Maestro;

final class ShortInit extends AbstractCacheBypassWithConstant
{
    public function __construct(Maestro $maestro)
    {
        //We don't support SHORTINIT. Flat out.
        parent::__construct($maestro, 'SHORTINIT');
    }
}
