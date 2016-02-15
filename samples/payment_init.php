<?php

    include( '../bootstrap.php' );

    S2P_SDK\S2P_SDK_Module::st_debugging_mode( true );
    S2P_SDK\S2P_SDK_Module::st_throw_errors( false );

    $api_parameters = array();

    // By default, API will check S2P_SDK_API_KEY, S2P_SDK_SITE_ID and S2P_SDK_ENVIRONMENT constats set in config.inc.php
    // If you want to override these constants (per request) uncomment lines below and provide values to override
    // $api_parameters['api_key'] = '{PROVIDED_APIKEY}';
    // $api_parameters['site_id'] = '{PROVIDED_SITE_ID}';
    // $api_parameters['environment'] = 'test'; // test or live

    $api_parameters['method'] = 'payments';
    $api_parameters['func'] = 'payment_init';

    $api_parameters['get_variables'] = array();
    $api_parameters['method_params'] = array(
        'payment' => array( // Mandatory
            'merchanttransactionid' => 'SDKtst_'.str_replace( '.', '', microtime( true ) ).'_'.rand(1000,9999),  // Mandatory (regexp: ^[0-9a-zA-Z_-]{1,50}$)
            'amount' => 1000,  // Mandatory in centimes (eg. 10.56 -> 1056)
            'currency' => 'EUR',  // Mandatory ISO 3 chars currency code (eg. EUR, GBP, USD, etc...)
            'returnurl' => (defined( 'S2P_SDK_PAYMENT_RETURN_URL' )?S2P_SDK_PAYMENT_RETURN_URL:''),  // Mandatory
            'methodid' => null,
            'siteid' => null,
            'description' => 'Demo payment',
            'customer' => array(
                'merchantcustomerid' => '',
                'email' => '',
                'firstname' => 'First Name',
                'lastname' => 'Last Name',
                'phone' => '',
                'company' => '',
            ),
            'billingaddress' => array(
                'country' => 'RO', // ISO 2 chars country code
                'city' => '',
                'zipcode' => '',
                'state' => '',
                'street' => '',
                'streetnumber' => '',
                'housenumber' => '',
                'houseextension' => '',
            ),
            'shippingaddress' => array(
                'country' => '',
                'city' => '',
                'zipcode' => '',
                'state' => '',
                'street' => '',
                'streetnumber' => '',
                'housenumber' => '',
                'houseextension' => '',
            ),
            'tokenlifetime' => 15,
        ),
    );

    $call_params = array();

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
        echo 'API call time: '.$call_result['call_microseconds'].'ms<br/>'."\n";

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
