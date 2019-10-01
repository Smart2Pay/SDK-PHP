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

    // !!! SmartCards note !!!
    // If $api_parameters['method_params']['payment']['methodid'] is 6
    // (SmartCards method - \S2P_SDK\S2P_SDK_Module::is_smartcards_method( $method_id )), you should normally send
    // $api_parameters['method'] = 'cards';. However, since SDK v2.1.23 $api_parameters['method'] will be aytomatically
    // changed to 'cards' in case 'payments' is provided

    $api_parameters['method'] = 'payments';
    $api_parameters['func'] = 'payment_init';

    $api_parameters['get_variables'] = array();
    $api_parameters['method_params'] = array(
        'payment' => array( // Mandatory
            'merchanttransactionid' => 'SDKtst_'.str_replace( '.', '', microtime( true ) ).'_'.mt_rand(1000,9999),  // Mandatory (regexp: ^[0-9a-zA-Z_-]{1,50}$)
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
    $call_params['curl_params'] = array(
        // In case you use proxy
        // 'proxy_server' => '8.8.8.8:888',
        // In case you need proxy authentication
        // 'proxy_auth' => 'user:pass',
        // For full access to cURL handler
        // 'curl_init_callback' => 'api_curl_extra_init',
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

/**
 * @param array $params_arr Value of 'ch' key is the cURL handler and 'params' key are parameters sent to \S2P_SDK\S2P_SDK_Rest_API_Request::do_curl() method
 *
 * @return bool|array If function call returns an array and on key 'params' there is an array, this array will replace exising $params array of parameters sent to \S2P_SDK\S2P_SDK_Rest_API_Request::do_curl() method.
 * !!! If you override 'params', be carefull not to break 'params' array as cURL call might fail.
 */
function api_curl_extra_init( $params_arr )
{
    if( empty( $params_arr ) or !is_array( $params_arr )
     or empty( $params_arr['ch'] )
     or !is_resource( $params_arr['ch'] ) )
        return false;

    // !!! Example on adding extra custom headers to cURL call
    // if( empty( $params_arr['params'] ) or !is_array( $params_arr['params'] ) )
    //     $params_arr['params'] = array();
    //
    // if( empty( $params_arr['params']['header_keys_arr'] ) or !is_array( $params_arr['params']['header_keys_arr'] ) )
    //     $params_arr['params']['header_keys_arr']['My-Header'] = 'MyValue';

    // !!! Example on adding extra cURL options
    //
    // @curl_setopt( $params_arr['ch'], CURLOPT_HTTPPROXYTUNNEL, 0 );
    // @curl_setopt( $params_arr['ch'], CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5 );

    return array(
        // Uncomment this line if you want to alter specific parameters of \S2P_SDK\S2P_SDK_Rest_API_Request::do_curl() method
        // Some parameters will be overridden by \S2P_SDK\S2P_SDK_Rest_API_Request::do_curl() method.
        // Check method to see what other parameters you can alter
        // 'params' => $params_arr['params'],
    );
}
