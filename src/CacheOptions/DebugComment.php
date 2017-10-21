<?php declare(strict_types=1);
namespace Vendi\Cache\CacheOptions;

class DebugComment extends AbstractCacheOption
{
    const COMMENT_ON  = 'on';

    const COMMENT_OFF  = 'off';

    public function get_option_type()
    {
        return self::OPTION_TYPE_CHECKBOX;
    }

    public function get_default_value()
    {
        return self::COMMENT_ON;
    }

    public function get_potential_options()
    {
        return [
                    self::COMMENT_ON,
                    self::COMMENT_OFF,
            ];
    }

    public function get_description()
    {
        return __('Add a hidden HTML comment to the bottom of every page.', 'vendi-cache');
    }

    public function get_storage_name()
    {
        return 'debug-comment';
    }

    public function get_true_value()
    {
        return self::COMMENT_ON;
    }
}
