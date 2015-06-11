<?php

    include( '../bootstrap.php' );

    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_demo.inc.php' );

    $demo = new S2P_SDK\S2P_SDK_Demo();

    // Uncomment following line and provide full URL path to SDK root directory
    // $demo->base_url( '{FULL_URL_TO_SDK_ROOT_DIRECTORY}' ); // eg. http://www.example.com/path/sdk/

    $demo->display_header();

    if( !$demo->display_init_payment_form() )
    {
        ?><p><?php
        if( ($error_arr = $demo->get_error()) )
            echo $error_arr['display_error'];
        else
            echo 'Error initiating form.';
        ?></p><?php
    }

    $demo->display_footer();