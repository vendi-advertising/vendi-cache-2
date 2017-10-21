<?php declare(strict_types=1);
namespace Vendi\Cache\CacheOptions;

class CacheMode extends AbstractCacheOption
{
    const MODE_OFF = 'off';

    const MODE_ON = 'on';

    public function get_option_type()
    {
        return self::OPTION_TYPE_RADIO;
    }

    public function get_default_value()
    {
        return self::MODE_OFF;
    }

    public function get_potential_options()
    {
        return [
                    self::MODE_OFF => __('Disable Vendi Cache', 'vendi-cache'),
                    self::MODE_ON  => __('Enable Vendi Cache', 'vendi-cache'),
            ];
    }

    public function get_description()
    {
        return __('Cache Mode', 'vendi-cache');
    }

    public function get_storage_name()
    {
        return 'cache-mode';
    }
}
