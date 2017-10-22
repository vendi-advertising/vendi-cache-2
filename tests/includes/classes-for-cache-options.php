<?php

namespace Vendi\Cache\Tests\CacheOptions;

use Vendi\Cache\Maestro;
use Vendi\Cache\CacheOptions\AbstractCacheOption;

abstract class generic_child_class_of_AbstractCacheOption extends AbstractCacheOption
{
    public function __construct( Maestro $maestro )
    {
        parent::__construct( $maestro->get_secretary() );
    }

    public function get_default_value()
    {
        return 'CHEESE';
    }

    public function get_potential_options()
    {
        return [
                     'CHEESE' => 'American',
                     'MEAT'   => 'Cow',
            ];
    }

    public function get_description()
    {
        return 'Test Child Class';
    }

    public function get_storage_name()
    {
        return 'test-child-class';
    }
}

final class radio_child_class_of_AbstractCacheOption extends generic_child_class_of_AbstractCacheOption
{
    public function get_option_type()
    {
        return self::OPTION_TYPE_RADIO;
    }
}

final class checkbox_child_class_of_AbstractCacheOption extends generic_child_class_of_AbstractCacheOption
{
    public function get_option_type()
    {
        return self::OPTION_TYPE_CHECKBOX;
    }

    public function get_true_value()
    {
        return 'CHEESE';
    }
}

final class unsupport_child_class_of_AbstractCacheOption extends generic_child_class_of_AbstractCacheOption
{
    public function get_option_type()
    {
        return 'TRIANGLE';
    }
}
