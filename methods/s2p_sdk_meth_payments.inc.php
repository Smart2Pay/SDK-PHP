<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_CLASSES' ) or !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_payment_request.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_payment_response.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_payment_response_list.inc.php' );
include_once( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' );
include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_api.inc.php' );
include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_values_source.inc.php' );

if( !defined( 'S2P_SDK_METH_PAYMENTS_INIT' ) )
    define( 'S2P_SDK_METH_PAYMENTS_INIT', 'payment_init' );
if( !defined( 'S2P_SDK_METH_PAYMENTS_CANCEL' ) )
    define( 'S2P_SDK_METH_PAYMENTS_CANCEL', 'payment_cancel' );
if( !defined( 'S2P_SDK_METH_PAYMENTS_DETAILS' ) )
    define( 'S2P_SDK_METH_PAYMENTS_DETAILS', 'payment_details' );
if( !defined( 'S2P_SDK_METH_PAYMENTS_LIST' ) )
    define( 'S2P_SDK_METH_PAYMENTS_LIST', 'payments_list' );
if( !defined( 'S2P_SDK_METH_PAYMENTS_CAPTURE' ) )
    define( 'S2P_SDK_METH_PAYMENTS_CAPTURE', 'payment_capture' );

class S2P_SDK_Meth_Payments extends S2P_SDK_Method
{
    const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    const FUNC_INIT_PAYMENT = S2P_SDK_METH_PAYMENTS_INIT, FUNC_CANCEL_PAYMENT = S2P_SDK_METH_PAYMENTS_CANCEL,
          FUNC_PAYMENT_DETAILS = S2P_SDK_METH_PAYMENTS_DETAILS, FUNC_LIST_PAYMENTS = S2P_SDK_METH_PAYMENTS_LIST,
          FUNC_PAYMENT_CAPTURE = S2P_SDK_METH_PAYMENTS_CAPTURE;

    const STATUS_OPEN = 1, STATUS_SUCCESS = 2, STATUS_CANCELLED = 3, STATUS_FAILED = 4, STATUS_EXPIRED = 5, STATUS_PROCESSING = 7, STATUS_AUTHORIZED = 9;

    public function default_functionality()
    {
        return self::FUNC_LIST_PAYMENTS;
    }

    /**
     * This method should be overridden by methods which have actions to be taken after we receive response from server
     *
     * @param array $call_result
     * @param array $params
     *
     * @return array Returns array with finalize action details
     */
    public function finalize( $call_result, $params )
    {
        $return_arr = self::default_finalize_result();

        if( !($call_result = S2P_SDK_Rest_API::validate_call_result( $call_result ))
         or empty( $call_result['response']['func'] ) )
            return $return_arr;

        switch( $call_result['response']['func'] )
        {
            case self::FUNC_INIT_PAYMENT:
                if( !empty( $call_result['response']['response_array']['payment'] )
                and !empty( $call_result['response']['response_array']['payment']['redirecturl'] ) )
                {
                    $return_arr['should_redirect'] = true;
                    $return_arr['redirect_to'] = $call_result['response']['response_array']['payment']['redirecturl'];
                }
            break;
        }

        return $return_arr;
    }

    /**
     * This method should be overridden by methods which have to check any errors in response data
     *
     * @param array $response_data
     *
     * @return bool Returns true if response doesn't have errors
     */
    public function validate_response( $response_data )
    {
        $response_data = self::validate_response_data( $response_data );

        switch( $response_data['func'] )
        {
            case self::FUNC_INIT_PAYMENT:
            case self::FUNC_CANCEL_PAYMENT:
            // in case we have an error for payments_list we will receive a payment object back
            case self::FUNC_LIST_PAYMENTS:
            case self::FUNC_PAYMENT_CAPTURE:
                if( !empty( $response_data['response_array']['payment'] ) )
                {
                    if( !empty( $response_data['response_array']['payment']['status'] )
                    and is_array( $response_data['response_array']['payment']['status'] ) )
                    {
                        if( !empty( $response_data['response_array']['payment']['status']['reasons'] )
                        and is_array( $response_data['response_array']['payment']['status']['reasons'] ) )
                        {
                            $error_msg = '';
                            foreach( $response_data['response_array']['payment']['status']['reasons'] as $reason_arr )
                            {
                                if( ( $error_reason = ( ! empty( $reason_arr['code'] ) ? $reason_arr['code'] . ' - ' : '' ) . ( ! empty( $reason_arr['info'] ) ? $reason_arr['info'] : '' ) ) != '' )
                                    $error_msg .= $error_reason;
                            }

                            if( ! empty( $error_msg ) )
                            {
                                $error_msg = self::s2p_t( 'Returned by server: %s', $error_msg );
                                $this->set_error( self::ERR_REASON_CODE, $error_msg );

                                return false;
                            }
                        }
                    }

                    if( empty( $response_data['response_array']['payment']['id'] ) )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'Payment ID is empty.' ) );
                        return false;
                    }

                    if( $response_data['func'] == self::FUNC_CANCEL_PAYMENT
                    and isset( $response_data['response_array']['payment']['status']['id'] )
                    and $response_data['response_array']['payment']['status']['id'] != self::STATUS_CANCELLED )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'Payment not cancelled.' ) );
                        return false;
                    }
                }
            break;
        }


        return true;
    }

    public function get_method_details()
    {
        return array(
            'method' => 'payments',
            'name' => self::s2p_t( 'Payments' ),
            'short_description' => self::s2p_t( 'This method manages payments' ),
        );
    }

    public function get_functionalities()
    {
        $payment_request_obj = new S2P_SDK_Structure_Payment_Request();
        $payment_response_obj = new S2P_SDK_Structure_Payment_Response();
        $payment_response_list_obj = new S2P_SDK_Structure_Payment_Response_List();

        return array(

            self::FUNC_INIT_PAYMENT => array(
                'name' => self::s2p_t( 'Initialize a Payment' ),
                'url_suffix' => '/v1/payments/',
                'http_method' => 'POST',

                'mandatory_in_request' => array(
                    'Payment' => array(
                        'MerchantTransactionID' => '',
                        'Amount' => '0',
                        'Currency' => '',
                        'ReturnURL' => '',
                    ),
                ),

                'context_from_request' => array(
                    'currency' => 'Payment.Currency',
                    'country' => 'Payment.Country',
                ),

                'request_structure' => $payment_request_obj,

                'mandatory_in_response' => array(
                    'payment' => array(),
                ),

                'response_structure' => $payment_response_obj,
            ),

            self::FUNC_PAYMENT_CAPTURE => array(
                'name' => self::s2p_t( 'Capture Payment' ),
                'url_suffix' => '/v1/payments/{*ID*}/capture',
                'http_method' => 'POST',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'response_structure' => $payment_response_obj,
            ),

            self::FUNC_CANCEL_PAYMENT => array(
                'name' => self::s2p_t( 'Cancel a Payment' ),
                'url_suffix' => '/v1/payments/{*ID*}/cancel/',
                'http_method' => 'POST',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'response_structure' => $payment_response_obj,
            ),

            self::FUNC_PAYMENT_DETAILS => array(
                'name' => self::s2p_t( 'Payment Details' ),
                'url_suffix' => '/v1/payments/{*ID*}/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'response_structure' => $payment_response_obj,
            ),

            self::FUNC_LIST_PAYMENTS => array(
                'name' => self::s2p_t( 'List Payments' ),
                'url_suffix' => '/v1/payments/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'limit',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'start_date',
                        'external_name' => 'startDate',
                        'type' => S2P_SDK_Scope_Variable::TYPE_DATETIME,
                        'default' => '',
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'end_date',
                        'external_name' => 'endDate',
                        'type' => S2P_SDK_Scope_Variable::TYPE_DATETIME,
                        'default' => '',
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'method_id',
                        'external_name' => 'methodID',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'country',
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => false,
                        'value_source' => S2P_SDK_Values_Source::TYPE_COUNTRY,
                    ),
                    array(
                        'name' => 'currency',
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => false,
                        'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
                    ),
                    array(
                        'name' => 'minimum_amount',
                        'external_name' => 'minimumAmount',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'maximum_amount',
                        'external_name' => 'maximumAmount',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'merchant_transaction_id',
                        'external_name' => 'merchantTransactionID',
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => false,
                    ),
                ),

                'mandatory_in_response' => array(
                    'payments' => array(),
                ),

                'response_structure' => $payment_response_list_obj,

                'mandatory_in_error' => array(
                    'payment' => array(),
                ),

                'error_structure' => $payment_response_obj,
            ),
       );
    }
}