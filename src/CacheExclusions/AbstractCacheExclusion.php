<?php

declare(strict_types=1);

namespace Vendi\Cache\CacheExclusions;

use Vendi\Cache\AbstractClassWithoutMagicGetSet;

abstract class AbstractCacheExclusion extends AbstractClassWithoutMagicGetSet
{
    abstract public function get_storage_name();
}
