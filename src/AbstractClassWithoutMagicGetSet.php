<?php

declare(strict_types=1);

namespace Vendi\Cache;

abstract class AbstractClassWithoutMagicGetSet
{
    /**
     * Override magic method so that we don't use incorrect property names.
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        throw new \Exception(sprintf(__('Attempt at setting undeclared property %1$s.', 'vendi-cache'), esc_html($name)));
    }

    /**
     * Override magic method so that we don't use incorrect property names.
     * @param mixed $name
     */
    public function __get($name)
    {
        throw new \Exception(sprintf(__('Attempt at getting undeclared property %1$s.', 'vendi-cache'), esc_html($name)));
    }
}
