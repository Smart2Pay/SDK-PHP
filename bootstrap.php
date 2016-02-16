<?php

    // Detecting current directory path - in certain environment setups you might have to manually provide this path
    if( !($_current_directory_path = __DIR__) )
        $_current_directory_path = getcwd();

    define( 'S2P_SDK_VERSION', '1.0.33' );

    define( 'S2P_SDK_DIR_PATH', $_current_directory_path.'/' );
    define( 'S2P_SDK_DIR_CLASSES', $_current_directory_path.'/classes/' );
    define( 'S2P_SDK_DIR_STRUCTURES', $_current_directory_path.'/structures/' );
    define( 'S2P_SDK_DIR_METHODS', $_current_directory_path.'/methods/' );
    define( 'S2P_SDK_DIR_LANGUAGES', $_current_directory_path.'/languages/' );

    include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_error.inc.php' );
    include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_language.inc.php' );
    include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_module.inc.php' );
    include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_database.inc.php' );
    include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_wrapper.inc.php' );

    include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_scope_variable.inc.php' );
    include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_scope_structure.inc.php' );

    if(
        !S2P_SDK\S2P_SDK_Language::define_language( S2P_SDK\S2P_SDK_Language::LANG_EN, array(
                    'title' => 'English',
                    'files' => array( S2P_SDK_DIR_LANGUAGES.'en.csv' ),
                ) )
        or

        !S2P_SDK\S2P_SDK_Language::define_language( S2P_SDK\S2P_SDK_Language::LANG_RO, array(
                    'title' => 'Romana',
                    'files' => array( S2P_SDK_DIR_LANGUAGES.'ro.csv' ),
                ) )
    )
    {
        // Do something if we cannot initialize English language
        S2P_SDK\S2P_SDK_Language::language_container()->throw_error();
    } else
    {
        S2P_SDK\S2P_SDK_Language::language_container()->set_current_language( S2P_SDK\S2P_SDK_Language::LANG_EN );
    }

    S2P_SDK\S2P_SDK_Module::st_debugging_mode( false );
    S2P_SDK\S2P_SDK_Module::st_detailed_errors( false );
    S2P_SDK\S2P_SDK_Module::st_throw_errors( false );

    if( !@file_exists( S2P_SDK_DIR_PATH.'config.inc.php' ) )
    {
        die( 'SDK config file not found. Please configure bootstrap file.' );
    }

    include_once( S2P_SDK_DIR_PATH.'config.inc.php' );
