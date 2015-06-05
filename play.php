<?php

    include_once( 'bootstrap.php' );

    try
    {
        /** @var S2P_SDK\S2P_SDK_Play $play_obj */
        if( !($play_obj = S2P_SDK\S2P_SDK_Module::get_instance( 'S2P_SDK_Play' ) ) )
        {
            if( ($error_arr = S2P_SDK\S2P_SDK_Module::st_get_error()) )
                die( 'Error initializing Play class: '.$error_arr['error_simple_msg'] );

            die( 'Error initializing Play class' );
        }

        $play_obj->play();
    } catch( Exception $ex )
    {
        die( 'Cought an error :'.$ex->getMessage() );
    }
