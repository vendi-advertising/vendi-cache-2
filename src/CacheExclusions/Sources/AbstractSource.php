<?php declare(strict_types=1);
namespace Vendi\Cache\CacheExclusions\Sources;

use Vendi\Cache\CacheExclusions\AbstractCacheExclusion;
use Vendi\Cache\CacheExclusions\Comparators\AbstractComparator;
use Vendi\Cache\Maestro;

abstract class AbstractSource extends AbstractCacheExclusion
{
    private $_maestro;

    abstract public function should_request_be_excluded_from_caching(AbstractComparator $source, $string_to_test);

    final public function __construct(Maestro $maestro)
    {
        $this->_maestro = $maestro;
    }

    final public function get_maestro() : Maestro
    {
        return $this->_maestro;
    }
}
