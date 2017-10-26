
<h2>Stats</h2>

<?php

$maestro = \Vendi\Cache\Maestro::get_default_instance();

dump(
        \Vendi\Cache\CacheStats::generate_from_file_system( $maestro )
    );
