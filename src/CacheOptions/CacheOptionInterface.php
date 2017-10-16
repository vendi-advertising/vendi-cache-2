<?php

namespace Vendi\Cache\CacheOptions;

use Vendi\Cache\Secretary;

interface CacheOptionInterface
{
    const OPTION_TYPE_CHECKBOX = 'checkbox';

    const OPTION_TYPE_RADIO = 'radio';

    public function get_potential_options();

    public function get_default_value();

    public function get_storage_name();

    public function is_value_valid($value);

    public function get_option_type();

    public function get_html();

    public function get_description();

    public function get_true_value();
}
