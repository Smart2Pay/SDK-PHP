<?php

    include( '../bootstrap.php' );

    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_demo.inc.php' );

    $demo = new S2P_SDK\S2P_SDK_Demo();

    $post_arr = $demo::extract_post_data();

    // Uncomment following line and provide full URL path to SDK root directory
    // $demo->base_url( '{FULL_URL_TO_SDK_ROOT_DIRECTORY}' ); // eg. http://www.example.com/path/sdk/

    $demo->display_header();

    $form_params = array();
    $form_params['post_params'] = $post_arr;
    $form_params['form_action_suffix'] = 'samples/init_payment.php';

    //if( ($form_buff = $demo->get_init_payment_form( $form_params )) )
    //    echo $form_buff;

    if( ($form_buff = $demo->get_form( $form_params )) )
        echo $form_buff;

    else
    {
        ?><p><?php
        if( ($error_arr = $demo->get_error()) )
            echo $error_arr['display_error'];
        else
            echo 'Error initiating form.';
        ?></p><?php
    }

    $demo->display_footer();