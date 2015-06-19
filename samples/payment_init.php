<?php

    include( '../bootstrap.php' );

    $api_parameters = array();

    // By default, API will check S2P_SDK_API_KEY and S2P_SDK_ENVIRONMENT constats set in config.inc.php
    // If you want to override constants uncomment lines below and provide values to override
    // $api_parameters['api_key'] = '{PROVIDED_APIKEY}';
    // $api_parameters['environment'] = 'test'; // test or live

    $api_parameters['method'] = 'payments';
    $api_parameters['func'] = 'payment_init';

    $api_parameters['get_variables'] = array();
    $api_parameters['method_params'] = array(
        'payment' => array( // Mandatory
            'merchanttransactionid' => '',  // Mandatory (regexp: ^[0-9a-zA-Z_-]{1,50}$)
            'amount' => 0,  // Mandatory in centimes (eg. 10.56 -> 1056)
            'currency' => '',  // Mandatory ISO 3 chars currency code (eg. EUR, GBP, USD, etc...)
            'returnurl' => '',  // Mandatory
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
                'country' => '', // ISO 2 chars country code
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
        ),
    );

    try
    {
        /** @var S2P_SDK\S2P_SDK_API $api */
        if( !($api = S2P_SDK\S2P_SDK_Module::get_instance( 'S2P_SDK_API', $api_parameters )) )
            var_dump( S2P_SDK\S2P_SDK_Module::st_get_error() );

        else
        {
            if( !$api->do_call() )
            {
                echo 'API call time: '.$api->get_call_time().'ms<br/>';
                var_dump( $api->get_error() );
            } else
            {
                $call_result = $api->get_result();

                $finalize_params = array();
                $finalize_params['redirect_now'] = true;

                // You should call $api->do_finalize() before sending headers if you want to be redirected to payment page...
                if( ($finalize_arr = $api->do_finalize( $finalize_params )) )
                {
                    // If $finalize_params['redirect_now'] is true and SDK should redirect after successfull API call you will not see these messages
                    // and will be redirected
                    echo 'Call result<br/>';
                    var_dump( $call_result );
                    echo 'Finalized transaction<br/>';
                    var_dump( $finalize_arr );
                } else
                {
                    echo 'Call result<br/>';
                    var_dump( $call_result );
                    echo 'Error finalizing<br/>';
                    var_dump( $api->get_error() );
                }


                echo 'API call time: '.$api->get_call_time().'ms<br/>';
                echo 'Successful API call:<br/><hr/><br/>';
                var_dump( $api->get_result() );
            }
        }
    } catch( Exception $ex )
    {
        var_dump( $ex );
    }
