
<h2>Stats</h2>

<?php

$maestro = \Vendi\Cache\Maestro::get_default_instance();

dump(
        \Vendi\Cache\CacheStats::generate_from_file_system( $maestro )
    );



// $fs = $maestro
//         ->get_file_system()
//     ;

// $fs
//     ->addPlugin( new \League\Flysystem\Plugin\ListWith() )
// ;

// $items = $fs->listWith([ 'mimetype', 'size', 'timestamp'], '', true );
// foreach( $items as $item )
// {
//     if( 'dir' === $item[ 'type' ] )
//     {
//         continue;
//     }

//     dump( $item );
// }

// // echo '<pre>';
// // print_r( $fs->listWith([ 'size', 'timestamp'], '', true ) );
// // echo '</pre>';
