<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_payment_request.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_payment_response.inc.php' );
include_once( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' );

if( !defined( 'S2P_SDK_METH_PAYMENTS_INIT' ) )
    define( 'S2P_SDK_METH_PAYMENTS_INIT', 'payment_init' );
if( !defined( 'S2P_SDK_METH_PAYMENTS_LIST' ) )
    define( 'S2P_SDK_METH_PAYMENTS_LIST', 'payments_list' );

class S2P_SDK_Meth_Payments extends S2P_SDK_Method
{
    const FUNC_INIT_PAYMENT = S2P_SDK_METH_PAYMENTS_INIT, FUNC_LIST_PAYMENTS = S2P_SDK_METH_PAYMENTS_LIST;

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
                'name' => self::s2p_t( 'Init Payments' ),
                'url_suffix' => '/v1/payments/',

                'mandatory_in_request' => array(
                    'Payment' => array(
                        'MerchantTransactionID' => '',
                        'Amount' => '0',
                        'Currency' => '',
                        'ReturnURL' => '',
                    ),
                ),

                'request_structure' => $payment_request_obj,

                'response_structure' => $payment_response_obj,
            ),

            self::FUNC_LIST_PAYMENTS => array(
                'name' => self::s2p_t( 'List Payments' ),
                'url_suffix' => '/v1/payments/',

                //'response_structure' => $payment_response_obj,
            ),
       );
    }
}