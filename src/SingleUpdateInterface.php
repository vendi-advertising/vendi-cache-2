<?php

namespace Vendi\Cache;

use Naneau\SemVer\Version\Versionable;
use SGH\Comparable\Comparable;

interface SingleUpdateInterface extends Comparable
{
    public function perform_update();

    public function get_update_version();
}
