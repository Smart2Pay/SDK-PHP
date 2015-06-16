<?php

    include( 'bootstrap.php' );

    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_demo.inc.php' );

    $demo = new S2P_SDK\S2P_SDK_Demo();

    // Uncomment following line and provide full URL path to SDK root directory
    // $demo->base_url( '{FULL_URL_TO_SDK_ROOT_DIRECTORY}' ); // eg. http://www.example.com/path/sdk/

    $post_arr = $demo::extract_post_data();

    $submit_params = array();
    $submit_params['post_arr'] = $post_arr;

    if( !($submit_result = $demo->handle_submit( $submit_params )) )
    {
        $submit_result = $demo::default_submit_result();
        if( $demo->has_error() and ($error_arr = $demo->get_error()) )
            $submit_result['errors_arr'] = array( $error_arr['display_error'] );
        else
            $submit_result['errors_arr'] = array( $demo::s2p_t( 'Error handling form submit.' ) );
    }

    $demo->display_header();

    $form_params = array();
    $form_params['post_params'] = $post_arr;
    $form_params['form_action_suffix'] = 'demo.php';
    $form_params['submit_result'] = $submit_result;

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