<?php

    // Detecting current directory path - in certain environment setups you might have to manually provide this path
    if( !($_current_directory_path = __DIR__)
    and !($_current_directory_path = @getcwd()) )
        $_current_directory_path = '.';

    $_current_directory_path = rtrim( $_current_directory_path, '/\\' ).'/';

    include_once( $_current_directory_path . 'classes/S2P_SDK_Error.php' );
    include_once( $_current_directory_path . 'classes/S2P_SDK_Language_Container.php' );
    include_once( $_current_directory_path . 'classes/S2P_SDK_Language.php' );
    include_once( $_current_directory_path . 'classes/S2P_SDK_Module.php' );

    if( !S2P_SDK\S2P_SDK_Module::sdk_init( $_current_directory_path ) )
    {
        echo 'Failed initializing Smart2Pay SDK.';
        exit;
    }

