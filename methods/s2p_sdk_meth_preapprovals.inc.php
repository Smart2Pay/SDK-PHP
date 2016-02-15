<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_preapproval_request.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_preapproval_response.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_preapproval_response_list.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_response_list.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_response.inc.php' );
include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_method.inc.php' );

if( !defined( 'S2P_SDK_METH_PREAPPROVALS_LIST_ALL' ) )
    define( 'S2P_SDK_METH_PREAPPROVALS_LIST_ALL', 'list_all' );
if( !defined( 'S2P_SDK_METH_PREAPPROVAL_INIT' ) )
    define( 'S2P_SDK_METH_PREAPPROVAL_INIT', 'preapproval_init' );
if( !defined( 'S2P_SDK_METH_PREAPPROVAL_DETAILS' ) )
    define( 'S2P_SDK_METH_PREAPPROVAL_DETAILS', 'preapproval_details' );
if( !defined( 'S2P_SDK_METH_PREAPPROVAL_PAYMENTS' ) )
    define( 'S2P_SDK_METH_PREAPPROVAL_PAYMENTS', 'preapproval_payments' );

class S2P_SDK_Meth_Preapprovals extends S2P_SDK_Method
{
    const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    const FUNC_INIT_PREAPPROVAL = S2P_SDK_METH_PREAPPROVAL_INIT, FUNC_LIST_ALL = S2P_SDK_METH_PREAPPROVALS_LIST_ALL, FUNC_DETAILS = S2P_SDK_METH_PREAPPROVAL_DETAILS,
          FUNC_PAYMENTS = S2P_SDK_METH_PREAPPROVAL_PAYMENTS;

    const STATUS_PENDING = 1, STATUS_OPEN = 2, STATUS_CLOSEDBYCUSTOMER = 4;

    private static $STATUSES_ARR = array(
        self::STATUS_PENDING => 'Pending',
        self::STATUS_OPEN => 'Open',
        self::STATUS_CLOSEDBYCUSTOMER => 'Closed By Customer',
    );

    public static function get_statuses()
    {
        return self::$STATUSES_ARR;
    }

    public static function valid_status( $status )
    {
        if( empty( $status )
         or !($statuses_arr = self::get_statuses()) or empty( $statuses_arr[$status] ) )
            return false;

        return $statuses_arr[$status];
    }

    public function default_functionality()
    {
        return self::FUNC_LIST_ALL;
    }

    public function get_notification_types()
    {
        $preapproval_notification_obj = new S2P_SDK_Structure_Preapproval_Response();

        return array(
            'Preapproval' => array(

                'request_structure' => $preapproval_notification_obj,

            )
        );
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
            case self::FUNC_INIT_PREAPPROVAL:
                if( !empty( $call_result['response']['response_array']['preapproval'] )
                and !empty( $call_result['response']['response_array']['preapproval']['redirecturl'] ) )
                {
                    $return_arr['should_redirect'] = true;
                    $return_arr['redirect_to'] = $call_result['response']['response_array']['preapproval']['redirecturl'];
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
            case self::FUNC_INIT_PREAPPROVAL:
                if( !empty( $response_data['response_array']['preapproval'] ) )
                {
                    if( !empty( $response_data['response_array']['preapproval']['status'] )
                    and is_array( $response_data['response_array']['preapproval']['status'] ) )
                    {
                        if( !empty( $response_data['response_array']['preapproval']['status']['reasons'] )
                        and is_array( $response_data['response_array']['preapproval']['status']['reasons'] ) )
                        {
                            $error_msg = '';
                            foreach( $response_data['response_array']['preapproval']['status']['reasons'] as $reason_arr )
                            {
                                if( ( $error_reason = ( ! empty( $reason_arr['code'] ) ? $reason_arr['code'] . ' - ' : '' ) . ( ! empty( $reason_arr['info'] ) ? $reason_arr['info'] : '' ) ) != '' )
                                    $error_msg .= $error_reason;
                            }

                            if( !empty( $error_msg ) )
                            {
                                $error_msg = self::s2p_t( 'Returned by server: %s', $error_msg );
                                $this->set_error( self::ERR_REASON_CODE, $error_msg );

                                return false;
                            }
                        }
                    }

                    if( empty( $response_data['response_array']['preapproval']['id'] ) )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'Preapproval ID is empty.' ) );
                        return false;
                    }

                    //if( $response_data['func'] == self::FUNC_CANCEL_PAYMENT
                    //and isset( $response_data['response_array']['payment']['status']['id'] )
                    //and $response_data['response_array']['payment']['status']['id'] != self::STATUS_CANCELLED )
                    //{
                    //    $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'Payment not cancelled.' ) );
                    //    return false;
                    //}
                }
            break;

            case self::FUNC_PAYMENTS:
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

                            if( !empty( $error_msg ) )
                            {
                                $error_msg = self::s2p_t( 'Returned by server: %s', $error_msg );
                                $this->set_error( self::ERR_REASON_CODE, $error_msg );

                                return false;
                            }
                        }
                    }
                }
            break;
        }

        return true;
    }

    public function get_method_details()
    {
        return array(
            'method' => 'preapprovals',
            'name' => self::s2p_t( 'Manage Preapprovals' ),
            'short_description' => self::s2p_t( 'This method manages preapprovals used in recurring payments' ),
        );
    }

    public function get_functionalities()
    {
        $preapproval_request_obj = new S2P_SDK_Structure_Preapproval_Request();
        $preapproval_response_obj = new S2P_SDK_Structure_Preapproval_Response();
        $preapproval_response_list_obj = new S2P_SDK_Structure_Preapproval_Response_List();
        $payment_response_obj = new S2P_SDK_Structure_Payment_Response();
        $payment_response_list_obj = new S2P_SDK_Structure_Payment_Response_List();

        return array(

            self::FUNC_LIST_ALL => array(
                'name' => self::s2p_t( 'List Preapprovals' ),
                'url_suffix' => '/v1/preapprovals/',
                'http_method' => 'GET',

                'mandatory_in_response' => array(
                    'preapprovals' => array(),
                ),

                'response_structure' => $preapproval_response_list_obj,
            ),

            self::FUNC_DETAILS => array(
                'name' => self::s2p_t( 'Preapproval Details' ),
                'url_suffix' => '/v1/preapprovals/{*ID*}',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Preapproval ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'preapproval' => array(),
                ),

                'response_structure' => $preapproval_response_obj,
            ),

            self::FUNC_PAYMENTS => array(
                'name' => self::s2p_t( 'Preapproval Payments List' ),
                'url_suffix' => '/v1/preapprovals/{*ID*}/payments',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Preapproval ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
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

            self::FUNC_INIT_PREAPPROVAL => array(
                'name' => self::s2p_t( 'Initiate a Preapproval' ),
                'url_suffix' => '/v1/preapprovals/',
                'http_method' => 'POST',

                'mandatory_in_request' => array(
                    'Preapproval' => array(
                        'MerchantPreapprovalID' => '',
                        'Description' => '',
                        'ReturnURL' => '',
                        'MethodID' => 0,
                        'Customer' => array(
                            'Email' => '',
                        ),
                        'BillingAddress' => array(
                            'Country' => '',
                        ),
                    ),
                ),

                'hide_in_request' => array(
                    'Preapproval' => array(
                        'Customer' => array(
                            'InputDateTime' => '',
                        ),
                        'Created' => '',
                        'Signature' => '',
                        'ApiKey' => '',
                        'Details' => '',
                    ),
                ),

                'request_structure' => $preapproval_request_obj,

                'mandatory_in_response' => array(
                    'preapproval' => array(),
                ),

                'response_structure' => $preapproval_response_obj,

                'mandatory_in_error' => array(
                    'preapproval' => array(),
                ),

                'error_structure' => $preapproval_response_obj,
            ),
       );
    }
}
