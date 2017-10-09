<?php

namespace Vendi\Cache;

use Naneau\SemVer\Compare;

abstract class AbstractSingleUpdate implements SingleUpdateInterface
{
    final public function compareTo( $object )
    {
        if( ! $object instanceof AbstractSingleUpdate )
        {
            throw new \Exception( 'Unable to compare two updates' );
        }

        $this_version = $this->get_update_version();
        $other_version = $obj->get_update_version();

        if( Compare::equals( $this_version, $other_version ) )
        {
            return 0;
        }

        if( Compare::greaterThan( $this_version, $other_version ) )
        {
            return 1;
        }

        if( Compare::smallerThan( $this_version, $other_version ) )
        {
            return -1;
        }

        throw new \Exception( 'Weird state, should never happen, comparison not expected' );
    }
}
