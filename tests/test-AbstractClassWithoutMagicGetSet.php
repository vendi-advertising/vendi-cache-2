<?php

declare(strict_types=1);

namespace Vendi\Cache\Tests;

use Vendi\Cache\AbstractClassWithoutMagicGetSet;

class test_3 extends AbstractClassWithoutMagicGetSet
{
}

class test_AbstractClassWithoutMagicGetSet extends vendi_cache_test_base
{
    /**
     * @covers \Vendi\Cache\AbstractClassWithoutMagicGetSet::__get()
     */
    public function test___get()
    {
        $this->setExpectedException('\Exception', 'Attempt at getting undeclared property xyz.');
        $x = $this->__get_mock()->xyz;
    }

    /**
     * @covers \Vendi\Cache\AbstractClassWithoutMagicGetSet::__set()
     */
    public function test___set()
    {
        $this->setExpectedException('\Exception', 'Attempt at setting undeclared property xyz.');
        $this->__get_mock()->xyz = 'xyz';
    }

    private function __get_mock()
    {
        return new test_3();
    }
}
