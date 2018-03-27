<?php

    include( '../bootstrap.php' );

    //
    // !!! Uncomment following lines if you don't want to create config.php file in SDK root folder. Scenario used in composer installs.
    // !!! Use SDK like this only if you cannot create config.php file in SDK root folder. If you use config.php file you will not have to always
    // !!! have these calls in each entry point that uses the SDK.
    //
    // define( 'S2P_SDK_SITE_ID', '{PROVIDED_SITE_ID}' );
    // define( 'S2P_SDK_API_KEY', '{PROVIDED_API_KEY}' );
    // define( 'S2P_SDK_ENVIRONMENT', 'test' ); // live or test
    //
    // You can also control debugging mode, detailed errors and error throwing
    //
    // Set SDK in debugging mode (or not)
    // S2P_SDK\S2P_SDK_Module::st_debugging_mode( false );
    // display full trace with the error (or not)
    // S2P_SDK\S2P_SDK_Module::st_detailed_errors( false );
    // Favor throwing errors when setting errors in classes (or not)
    // S2P_SDK\S2P_SDK_Module::st_throw_errors( false );
    //

    S2P_SDK\S2P_SDK_Module::st_debugging_mode( true );
    S2P_SDK\S2P_SDK_Module::st_throw_errors( false );

    $api_parameters = array();

    // By default, API will check S2P_SDK_API_KEY, S2P_SDK_SITE_ID and S2P_SDK_ENVIRONMENT constats set in config.php
    // If you want to override these constants (per request) uncomment lines below and provide values to override
    // $api_parameters['api_key'] = '{PROVIDED_APIKEY}';
    // $api_parameters['site_id'] = '{PROVIDED_SITE_ID}';
    // $api_parameters['environment'] = 'test'; // test or live

    $api_parameters['method'] = 'methods';
    $api_parameters['func'] = 'list_all';

    $api_parameters['get_variables'] = array();
    $api_parameters['method_params'] = array();

    $call_params = array();
    $call_params['curl_params'] = array(
        // Use constant function so in case constant is not set, it will be empty and cURL call function would choose a default value
        'connection_ssl_version' => constant( 'CURL_SSLVERSION_TLSv1_2' ),
    );

    $finalize_params = array();
    $finalize_params['redirect_now'] = false;

    if( !($call_result = S2P_SDK\S2P_SDK_Module::quick_call( $api_parameters, $call_params, $finalize_params )) )
    {
        echo 'API call error: ';

        if( ($error_arr = S2P_SDK\S2P_SDK_Module::st_get_error())
        and !empty( $error_arr['display_error'] ) )
            echo $error_arr['display_error'];
        else
            echo 'Unknown error.';
    } else
    {
        echo 'API call time: '.$call_result['call_microseconds'].'s<br/>'."\n";

        if( !empty( $call_result['finalize_result']['should_redirect'] )
        and !empty( $call_result['finalize_result']['redirect_to'] ) )
            echo '<br/>'."\n".
                 'Go to <a href="'.$call_result['finalize_result']['redirect_to'].'">'.$call_result['finalize_result']['redirect_to'].'</a> to complete transaction<br/>'."\n".
                 '<br/>'."\n";

        echo 'Call result:<br>'."\n".
             '<pre>';

        var_dump( $call_result['call_result'] );

        echo '</pre>';
    }
