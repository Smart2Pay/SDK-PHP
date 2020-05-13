<?php

namespace S2P_SDK;

class S2P_SDK_Meth_Cards extends S2P_SDK_Method
{
    const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    const FUNC_PAYMENT_INIT = 'payment_init', FUNC_PAYMENT_CANCEL = 'payment_cancel', FUNC_PAYMENT_STATUS = 'payment_status',
          FUNC_PAYMENT_DETAILS = 'payment_details', FUNC_PAYMENTS_LIST = 'payments_list',
          FUNC_CAPTURE_PAYMENT = 'payment_capture', FUNC_EDIT_PAYMENT = 'payment_edit',

          FUNC_REFUND_INIT = 'refund_init', FUNC_REFUNDS_LIST = 'refunds_list', FUNC_REFUND_DETAILS = 'refund_details', FUNC_REFUND_STATUS = 'refund_status',

          FUNC_PAYOUT_INIT = 'payout_init', FUNC_PAYOUT_LIST = 'payouts_list', FUNC_PAYOUT_DETAILS = 'payout_details', FUNC_PAYOUT_STATUS = 'payout_status',

          FUNC_CARDAUTH_INIT = 'card_auth_init', FUNC_CARDAUTH_DETAILS = 'card_auth_details', FUNC_CARDAUTH_CANCEL = 'card_auth_cancel',

          FUNC_CAPUTES_LIST = 'captures_list', FUNC_CAPTURE_DETAILS = 'capture_details';

    const STATUS_OPEN = 1, STATUS_SUCCESS = 2, STATUS_CANCELLED = 3, STATUS_FAILED = 4, STATUS_EXPIRED = 5,
          STATUS_PENDING_PROVIDER = 7, STATUS_AUTHORIZED = 9, STATUS_PENDING_AUTORIZE = 10, STATUS_CAPTURED = 11,
          STATUS_PENDING_CAPTURE = 13, STATUS_EXCEPTION = 14, STATUS_PENDING_CANCEL = 15, STATUS_REVERSED = 16,
          STATUS_DISPUTED = 19, STATUS_PARTIALLY_REFUNDED = 21, STATUS_REFUNDED = 22, STATUS_CHARGEBACK_WON = 23,
          STATUS_CHARGEBACK_LOST = 24, STATUS_PAID = 25, STATUS_CHARGED_BACK = 26, STATUS_SECOND_CHARGEBACK_WON = 27,
          STATUS_SECOND_CHARGEBACK_LOST = 28, STATUS_PENDING_CHALLENGE_CONFIRMATION = 30, STATUS_QUEUED_CAPTURE = 33,
          STATUS_QUEUED_CANCEL = 34, STATUS_PARTIALLY_CAPTURED = 35;

    private static $STATUSES_ARR = array(
        self::STATUS_OPEN => 'Open',
        self::STATUS_SUCCESS => 'Success',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_FAILED => 'Failed',
        self::STATUS_EXPIRED => 'Expired',
        self::STATUS_PENDING_PROVIDER => 'Pending on Provider',
        self::STATUS_AUTHORIZED => 'Authorized',
        self::STATUS_PENDING_AUTORIZE => 'Pending Authorization',
        self::STATUS_CAPTURED => 'Captured',
        self::STATUS_PENDING_CAPTURE => 'Pending Capture',
        self::STATUS_EXCEPTION => 'Exception',
        self::STATUS_PENDING_CANCEL => 'Pending Cancellation',
        self::STATUS_REVERSED => 'Reversed',
        self::STATUS_DISPUTED => 'Disputed',
        self::STATUS_PARTIALLY_REFUNDED => 'Partially Refunded',
        self::STATUS_REFUNDED => 'Refunded',
        self::STATUS_CHARGEBACK_WON => 'Chargeback Won',
        self::STATUS_CHARGEBACK_LOST => 'Chargeback Lost',
        self::STATUS_PAID => 'Paid',
        self::STATUS_CHARGED_BACK => 'Charged Back',
        self::STATUS_SECOND_CHARGEBACK_WON => 'Second Chargeback Won',
        self::STATUS_SECOND_CHARGEBACK_LOST => 'Second Chargeback Lost',
        self::STATUS_PENDING_CHALLENGE_CONFIRMATION => 'Pending Challenge Confirmation',
        self::STATUS_QUEUED_CAPTURE => 'Queued For Capturing',
        self::STATUS_QUEUED_CANCEL => 'Queued For Cancelling',
        self::STATUS_PARTIALLY_CAPTURED => 'Partially Captured',
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

    /**
     * Tells which entry point does this method use
     * @return string
     */
    public function get_entry_point()
    {
        return S2P_SDK_Rest_API::ENTRY_POINT_CARDS;
    }

    public function default_functionality()
    {
        return self::FUNC_PAYMENTS_LIST;
    }

    /**
     * @inheritdoc
     */
    public function get_notification_types()
    {
        $payment_notification_obj = new S2P_SDK_Structure_Payment_Response();
        $dispute_notification_obj = new S2P_SDK_Structure_Dispute_Notification();

        return array(
            'Payment' => array(

                'request_structure' => $payment_notification_obj,

            ),
            'Dispute' => array(

                'request_structure' => $dispute_notification_obj,

            ),
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
            case self::FUNC_PAYMENT_INIT:
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
            case self::FUNC_PAYMENT_INIT:
            case self::FUNC_PAYMENT_CANCEL:
            // in case we have an error for payments_list we will receive a payment object back
            case self::FUNC_PAYMENTS_LIST:
            case self::FUNC_CAPTURE_PAYMENT:
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

                    if( empty( $response_data['request_http_code'] )
                     or !in_array( $response_data['request_http_code'], S2P_SDK_Rest_API_Codes::success_codes() ) )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'API call failed with error code %s.', $response_data['request_http_code'] ) );
                        return false;
                    }

                    if( empty( $response_data['response_array']['payment']['id'] ) )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'Payment ID is empty.' ) );
                        return false;
                    }

                    if( $response_data['func'] === self::FUNC_PAYMENT_CANCEL
                    and isset( $response_data['response_array']['payment']['status']['id'] )
                    and (int)$response_data['response_array']['payment']['status']['id'] !== self::STATUS_CANCELLED )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'Payment not cancelled.' ) );
                        return false;
                    }
                }
            break;

            case self::FUNC_REFUNDS_LIST:
            case self::FUNC_REFUND_INIT:
            case self::FUNC_REFUND_DETAILS:
                if( !empty( $response_data['response_array']['refund'] ) )
                {
                    if( !empty( $response_data['response_array']['refund']['status'] )
                    and is_array( $response_data['response_array']['refund']['status'] ) )
                    {
                        if( !empty( $response_data['response_array']['refund']['status']['reasons'] )
                        and is_array( $response_data['response_array']['refund']['status']['reasons'] ) )
                        {
                            $error_msg = '';
                            foreach( $response_data['response_array']['refund']['status']['reasons'] as $reason_arr )
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

                    if( empty( $response_data['response_array']['refund']['id'] ) )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'Refund ID is empty.' ) );
                        return false;
                    }
                }
            break;

            case self::FUNC_PAYOUT_INIT:
            case self::FUNC_PAYOUT_STATUS:
            case self::FUNC_PAYOUT_DETAILS:
                if( !empty( $response_data['response_array']['payout'] ) )
                {
                    if( !empty( $response_data['response_array']['payout']['status'] )
                    and is_array( $response_data['response_array']['payout']['status'] ) )
                    {
                        if( !empty( $response_data['response_array']['payout']['status']['reasons'] )
                        and is_array( $response_data['response_array']['payout']['status']['reasons'] ) )
                        {
                            $error_msg = '';
                            foreach( $response_data['response_array']['payout']['status']['reasons'] as $reason_arr )
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

                    if( empty( $response_data['response_array']['payout']['id'] ) )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'Payout ID is empty.' ) );
                        return false;
                    }
                }
            break;

            case self::FUNC_CARDAUTH_INIT:
            case self::FUNC_CARDAUTH_DETAILS:
            case self::FUNC_CARDAUTH_CANCEL:
                if( !empty( $response_data['response_array']['cardauthentication'] ) )
                {
                    if( !empty( $response_data['response_array']['cardauthentication']['status'] )
                    and is_array( $response_data['response_array']['cardauthentication']['status'] ) )
                    {
                        if( !empty( $response_data['response_array']['cardauthentication']['status']['reasons'] )
                        and is_array( $response_data['response_array']['cardauthentication']['status']['reasons'] ) )
                        {
                            $error_msg = '';
                            foreach( $response_data['response_array']['cardauthentication']['status']['reasons'] as $reason_arr )
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

                    if( empty( $response_data['response_array']['cardauthentication']['credicardtoken'] )
                     or !is_array( $response_data['response_array']['cardauthentication']['credicardtoken'] )
                     or empty( $response_data['response_array']['cardauthentication']['credicardtoken']['value'] ) )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'Credit card token is empty.' ) );
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
            'method' => 'cards',
            'name' => self::s2p_t( 'Cards' ),
            'short_description' => self::s2p_t( 'This method manages card payments' ),
        );
    }

    public function get_functionalities()
    {
        $card_payment_request_obj = new S2P_SDK_Structure_Card_Payment_Request();
        $payment_response_obj = new S2P_SDK_Structure_Payment_Response();
        $payment_response_list_obj = new S2P_SDK_Structure_Payment_Response_List();
        $refund_request_obj = new S2P_SDK_Structure_Card_Refund_Request();
        $refund_response_obj = new S2P_SDK_Structure_Card_Refund_Response();
        $refund_response_list_obj = new S2P_SDK_Structure_Card_Refund_Response_List();
        $payout_request_obj = new S2P_SDK_Structure_Payout_Request();
        $payout_response_obj = new S2P_SDK_Structure_Payout_Response();
        $payout_response_list_obj = new S2P_SDK_Structure_Payout_Response_List();

        $card_auth_request_obj = new S2P_SDK_Structure_Card_Authentication_Request();
        $card_auth_response_obj = new S2P_SDK_Structure_Card_Authentication_Response();
        $capture_response_obj = new S2P_SDK_Structure_Capture_Response();
        $capture_response_list_obj = new S2P_SDK_Structure_Capture_Response_List();

        return array(

            self::FUNC_PAYMENT_INIT => array(
                'name' => self::s2p_t( 'Initiate a Card Payment' ),
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

                'request_structure' => $card_payment_request_obj,

                'hide_in_request' => array(
                    'Payment' => array(
                        'Customer' => array(
                            'InputDateTime' => '',
                        ),
                    ),
                ),

                'mandatory_in_response' => array(
                    'payment' => array(),
                ),

                'response_structure' => $payment_response_obj,

                'mandatory_in_error' => array(
                    'payment' => array(),
                ),

                'error_structure' => $payment_response_obj,
            ),

            self::FUNC_PAYMENT_DETAILS => array(
                'name' => self::s2p_t( 'Card Payment Details' ),
                'url_suffix' => '/v1/payments/{*ID*}/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Payment ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'response_structure' => $payment_response_obj,
            ),

            self::FUNC_PAYMENT_STATUS => array(
                'name' => self::s2p_t( 'Card Payment Status' ),
                'url_suffix' => '/v1/payments/{*ID*}/statuss',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Payment ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'response_structure' => $payment_response_obj,
            ),

            self::FUNC_CAPTURE_PAYMENT => array(
                'name' => self::s2p_t( 'Capture Card Payment' ),
                'url_suffix' => '/v1/payments/{*ID*}/capture',
                'http_method' => 'POST',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Payment ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'response_structure' => $payment_response_obj,

                'mandatory_in_error' => array(
                    'payment' => array(),
                ),

                'error_structure' => $payment_response_obj,
            ),

            self::FUNC_PAYMENT_CANCEL => array(
                'name' => self::s2p_t( 'Cancel a Card Payment' ),
                'url_suffix' => '/v1/payments/{*ID*}/cancel/',
                'http_method' => 'POST',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Payment ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'response_structure' => $payment_response_obj,

                'mandatory_in_error' => array(
                    'payment' => array(),
                ),

                'error_structure' => $payment_response_obj,
            ),

            self::FUNC_PAYMENTS_LIST => array(
                'name' => self::s2p_t( 'List Card Payments' ),
                'url_suffix' => '/v1/payments/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'limit',
                        'display_name' => self::s2p_t( 'Rows limit' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'start_date',
                        'external_name' => 'startDate',
                        'display_name' => self::s2p_t( 'Interval starting date' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_DATETIME,
                        'default' => '',
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'end_date',
                        'external_name' => 'endDate',
                        'display_name' => self::s2p_t( 'Interval ending date' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_DATETIME,
                        'default' => '',
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'country',
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'display_name' => self::s2p_t( 'Country' ),
                        'default' => '',
                        'mandatory' => false,
                        'value_source' => S2P_SDK_Values_Source::TYPE_COUNTRY,
                    ),
                    array(
                        'name' => 'currency',
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'display_name' => self::s2p_t( 'Currency' ),
                        'default' => '',
                        'mandatory' => false,
                        'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
                    ),
                    array(
                        'name' => 'minimum_amount',
                        'external_name' => 'minimumAmount',
                        'display_name' => self::s2p_t( 'Minimum amount' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'maximum_amount',
                        'external_name' => 'maximumAmount',
                        'display_name' => self::s2p_t( 'Maximum amount' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => false,
                    ),
                    array(
                        'name' => 'merchant_transaction_id',
                        'external_name' => 'merchantTransactionID',
                        'display_name' => self::s2p_t( 'Merchant transaction ID' ),
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

            self::FUNC_REFUND_INIT => array(
                'name' => self::s2p_t( 'Initiate a Card Payment Refund' ),
                'url_suffix' => '/v1/payments/{*ID*}/refunds',
                'http_method' => 'POST',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Payment ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_request' => array(
                    'Refund' => array(
                        'MerchantTransactionID' => '',
                        'Amount' => '0',
                    ),
                ),

                'request_structure' => $refund_request_obj,

                'mandatory_in_response' => array(
                    'refund' => array(),
                ),

                'response_structure' => $refund_response_obj,

                'mandatory_in_error' => array(
                    'refund' => array(),
                ),

                'error_structure' => $refund_response_obj,
            ),

            self::FUNC_REFUNDS_LIST => array(
                'name' => self::s2p_t( 'List Card Payment Refunds' ),
                'url_suffix' => '/v1/payments/{*ID*}/refunds',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Payment ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'refunds' => array(),
                ),

                'response_structure' => $refund_response_list_obj,

                'mandatory_in_error' => array(
                    'refund' => array(),
                ),

                'error_structure' => $refund_response_obj,
            ),

            self::FUNC_REFUND_DETAILS => array(
                'name' => self::s2p_t( 'Card Payment Refund Details' ),
                'url_suffix' => '/v1/payments/{*PAYMENT_ID*}/refunds/{*ID*}',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'payment_id',
                        'display_name' => self::s2p_t( 'Payment ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Refund ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'refund' => array(),
                ),

                'response_structure' => $refund_response_obj,

                'mandatory_in_error' => array(
                    'refund' => array(),
                ),

                'error_structure' => $refund_response_obj,
            ),

            self::FUNC_REFUND_STATUS => array(
                'name' => self::s2p_t( 'Get Cart Payment Refund Status' ),
                'url_suffix' => '/v1/refunds/{*ID*}/status',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Refund ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'refund' => array(),
                ),

                'response_structure' => $refund_response_obj,

                'mandatory_in_error' => array(
                    'refund' => array(),
                ),

                'error_structure' => $refund_response_obj,
            ),

            self::FUNC_PAYOUT_INIT => array(
                'name' => self::s2p_t( 'Initiate a Payout' ),
                'url_suffix' => '/v1/payouts',
                'http_method' => 'POST',

                'mandatory_in_request' => array(
                    'Payout' => array(
                        'MerchantTransactionID' => '',
                        'Amount' => '0',
                        'Currency' => '',
                    ),
                ),

                'request_structure' => $payout_request_obj,

                'hide_in_request' => array(
                    'Payout' => array(
                        'Customer' => array(
                            'InputDateTime' => '',
                        ),
                    ),
                ),

                'mandatory_in_response' => array(
                    'payout' => array(),
                ),

                'response_structure' => $payout_response_obj,

                'mandatory_in_error' => array(
                    'payout' => array(),
                ),

                'error_structure' => $payout_response_obj,
            ),

            self::FUNC_PAYOUT_LIST => array(
                'name' => self::s2p_t( 'List Payouts' ),
                'url_suffix' => '/v1/payouts',
                'http_method' => 'GET',

                'mandatory_in_response' => array(
                    'payouts' => array(),
                ),

                'response_structure' => $payout_response_list_obj,

                'mandatory_in_error' => array(
                    'payout' => array(),
                ),

                'error_structure' => $payout_response_obj,
            ),

            self::FUNC_PAYOUT_DETAILS => array(
                'name' => self::s2p_t( 'Payout Details' ),
                'url_suffix' => '/v1/payouts/{*ID*}',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Payout ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'payout' => array(),
                ),

                'response_structure' => $payout_response_obj,

                'mandatory_in_error' => array(
                    'payout' => array(),
                ),

                'error_structure' => $payout_response_obj,
            ),

            self::FUNC_PAYOUT_STATUS => array(
                'name' => self::s2p_t( 'Get Payout Status' ),
                'url_suffix' => '/v1/payouts/{*ID*}/status',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Payout ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'payout' => array(),
                ),

                'response_structure' => $payout_response_obj,

                'mandatory_in_error' => array(
                    'payout' => array(),
                ),

                'error_structure' => $payout_response_obj,
            ),

            self::FUNC_CARDAUTH_INIT => array(
                'name' => self::s2p_t( 'Initiate a Card Authentication' ),
                'url_suffix' => '/v1/card/authenticate',
                'http_method' => 'POST',

                'mandatory_in_request' => array(
                    'CardAuthentication' => array(
                        'Card' => array(
                            'Number' => '',
                        ),
                    ),
                ),

                'request_structure' => $card_auth_request_obj,

                'mandatory_in_response' => array(
                    'card_authentication' => array(),
                ),

                'response_structure' => $card_auth_response_obj,

                'mandatory_in_error' => array(
                    'card_authentication' => array(),
                ),

                'error_structure' => $card_auth_response_obj,
            ),

            self::FUNC_CARDAUTH_DETAILS => array(
                'name' => self::s2p_t( 'Get Card Authentication Details' ),
                'url_suffix' => '/v1/card/token/{*TOKEN*}',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'token',
                        'display_name' => self::s2p_t( 'Card Authentication Token' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'card_authentication' => array(),
                ),

                'response_structure' => $card_auth_response_obj,

                'mandatory_in_error' => array(
                    'card_authentication' => array(),
                ),

                'error_structure' => $card_auth_response_obj,
            ),

            self::FUNC_CARDAUTH_CANCEL => array(
                'name' => self::s2p_t( 'Get Cart Payment Refund Status' ),
                'url_suffix' => '/v1/card/token/{*TOKEN*}/cancel',
                'http_method' => 'POST',

                'get_variables' => array(
                    array(
                        'name' => 'token',
                        'display_name' => self::s2p_t( 'Card Authentication Token' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'card_authentication' => array(),
                ),

                'response_structure' => $card_auth_response_obj,

                'mandatory_in_error' => array(
                    'card_authentication' => array(),
                ),

                'error_structure' => $card_auth_response_obj,
            ),

            self::FUNC_CAPUTES_LIST => array(
                'name' => self::s2p_t( 'List Card Payment Captures' ),
                'url_suffix' => '/v1/payments/{*ID*}/captures',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Payment ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'captures' => array(),
                ),

                'response_structure' => $capture_response_list_obj,
            ),

            self::FUNC_CAPTURE_DETAILS => array(
                'name' => self::s2p_t( 'Payment Card Capture Details' ),
                'url_suffix' => '/v1/payments/{*PAYMENT_ID*}/captures/{*ID*}',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'payment_id',
                        'display_name' => self::s2p_t( 'Payment ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Capture ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'response_structure' => $capture_response_obj,
            ),

        );
    }
}
