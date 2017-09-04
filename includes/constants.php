<?php

//You may define this elsewhere if you want to store the cache in a completely
//different folder, otherwise it will be stored relative to wp-content.
if( ! defined( 'VENDI_CACHE_FOLDER_ABS' ) )
{
    //You may define this elsewhere if you want to change the folder name
    //relative to wp-content.
    if( ! defined( 'VENDI_CACHE_FOLDER_NAME' ) )
    {
        define( 'VENDI_CACHE_FOLDER_NAME', 'vendi_cache');
    }

    define( 'VENDI_CACHE_FOLDER_ABS', \Webmozart\PathUtil\Path::join( WP_CONTENT_DIR, VENDI_CACHE_FOLDER_NAME ) );
}
