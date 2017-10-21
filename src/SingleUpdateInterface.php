<?php declare(strict_types=1);
namespace Vendi\Cache;

use SGH\Comparable\Comparable;

interface SingleUpdateInterface extends Comparable
{
    public function perform_update();

    public function get_update_version();
}
