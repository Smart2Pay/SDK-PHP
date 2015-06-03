<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_payment_request.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_payment_response.inc.php' );
include_once( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' );

class S2P_SDK_Meth_Payments extends S2P_SDK_Method
{
    const FUNC_INIT_PAYMENT = 1;

    public function default_functionality()
    {
        return self::FUNC_INIT_PAYMENT;
    }

    public function get_functionalities()
    {
        $payment_request_obj = new S2P_SDK_Structure_Payment_Request();
        $payment_response_obj = new S2P_SDK_Structure_Payment_Response();

        return array(

            self::FUNC_INIT_PAYMENT => array(
                'name' => 'payments_init',
                'url_suffix' => '/v1/payments/',

                'mandatory_in_request' => array(
                    'payment' => array(
                        'merchanttransactionid' => '',
                        'amount' => '0',
                        'currency' => '',
                        'returnurl' => '',
                    ),
                ),

                'request_structure' => $payment_request_obj,

                'response_structure' => $payment_response_obj,
            ),
       );
    }
}