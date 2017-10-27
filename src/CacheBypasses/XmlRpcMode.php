<?php declare(strict_types=1);
namespace Vendi\Cache\CacheBypasses;

use Vendi\Cache\Maestro;

final class XmlRpcMode extends AbstractCacheBypassWithConstant
{
    public function __construct(Maestro $maestro)
    {
        parent::__construct($maestro, 'XMLRPC_REQUEST');
    }
}
