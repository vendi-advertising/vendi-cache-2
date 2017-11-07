<?php declare(strict_types=1);
namespace Vendi\Cache;

abstract class AbstractMaestroEnabledBase
{
    private $_maestro;

    public function __construct(Maestro $maestro)
    {
        $this->_maestro = $maestro;
    }

    public function get_maestro()
    {
        return $this->_maestro;
    }
}
