<?php
namespace S2P_SDK;

class S2P_SDK_Meth_Cards extends S2P_SDK_Method
{
    public const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    public const FUNC_PAYMENT_INIT = 'payment_init', FUNC_PAYMENT_CANCEL = 'payment_cancel', FUNC_PAYMENT_STATUS = 'payment_status',
        FUNC_PAYMENT_DETAILS = 'payment_details', FUNC_PAYMENTS_LIST = 'payments_list',
        FUNC_CAPTURE_PAYMENT = 'payment_capture', FUNC_EDIT_PAYMENT = 'payment_edit',
        FUNC_SPLIT_CAPTURE = 'split_capture', FUNC_SPLIT_REFUND = 'split_refund',

        FUNC_REFUND_INIT = 'refund_init', FUNC_REFUNDS_LIST = 'refunds_list', FUNC_REFUND_DETAILS = 'refund_details', FUNC_REFUND_STATUS = 'refund_status',

        FUNC_PAYOUT_INIT = 'payout_init', FUNC_PAYOUT_LIST = 'payouts_list', FUNC_PAYOUT_DETAILS = 'payout_details', FUNC_PAYOUT_STATUS = 'payout_status',

        FUNC_CARDAUTH_INIT = 'card_auth_init', FUNC_CARDAUTH_DETAILS = 'card_auth_details', FUNC_CARDAUTH_CANCEL = 'card_auth_cancel',

        FUNC_CAPUTES_LIST = 'captures_list', FUNC_CAPTURE_DETAILS = 'capture_details';

    public const STATUS_OPEN = 1, STATUS_SUCCESS = 2, STATUS_CANCELLED = 3, STATUS_FAILED = 4, STATUS_EXPIRED = 5,
        STATUS_PENDING_PROVIDER = 7, STATUS_AUTHORIZED = 9, STATUS_PENDING_AUTORIZE = 10, STATUS_CAPTURED = 11,
        STATUS_PENDING_CAPTURE = 13, STATUS_EXCEPTION = 14, STATUS_PENDING_CANCEL = 15, STATUS_REVERSED = 16,
        STATUS_DISPUTED = 19, STATUS_PARTIALLY_REFUNDED = 21, STATUS_REFUNDED = 22, STATUS_CHARGEBACK_WON = 23,
        STATUS_CHARGEBACK_LOST = 24, STATUS_PAID = 25, STATUS_CHARGED_BACK = 26, STATUS_SECOND_CHARGEBACK_WON = 27,
        STATUS_SECOND_CHARGEBACK_LOST = 28, STATUS_PENDING_CHALLENGE_CONFIRMATION = 30, STATUS_QUEUED_CAPTURE = 33,
        STATUS_QUEUED_CANCEL = 34, STATUS_PARTIALLY_CAPTURED = 35;

    private static $STATUSES_ARR = [
        self::STATUS_OPEN                           => 'Open',
        self::STATUS_SUCCESS                        => 'Success',
        self::STATUS_CANCELLED                      => 'Cancelled',
        self::STATUS_FAILED                         => 'Failed',
        self::STATUS_EXPIRED                        => 'Expired',
        self::STATUS_PENDING_PROVIDER               => 'Pending on Provider',
        self::STATUS_AUTHORIZED                     => 'Authorized',
        self::STATUS_PENDING_AUTORIZE               => 'Pending Authorization',
        self::STATUS_CAPTURED                       => 'Captured',
        self::STATUS_PENDING_CAPTURE                => 'Pending Capture',
        self::STATUS_EXCEPTION                      => 'Exception',
        self::STATUS_PENDING_CANCEL                 => 'Pending Cancellation',
        self::STATUS_REVERSED                       => 'Reversed',
        self::STATUS_DISPUTED                       => 'Disputed',
        self::STATUS_PARTIALLY_REFUNDED             => 'Partially Refunded',
        self::STATUS_REFUNDED                       => 'Refunded',
        self::STATUS_CHARGEBACK_WON                 => 'Chargeback Won',
        self::STATUS_CHARGEBACK_LOST                => 'Chargeback Lost',
        self::STATUS_PAID                           => 'Paid',
        self::STATUS_CHARGED_BACK                   => 'Charged Back',
        self::STATUS_SECOND_CHARGEBACK_WON          => 'Second Chargeback Won',
        self::STATUS_SECOND_CHARGEBACK_LOST         => 'Second Chargeback Lost',
        self::STATUS_PENDING_CHALLENGE_CONFIRMATION => 'Pending Challenge Confirmation',
        self::STATUS_QUEUED_CAPTURE                 => 'Queued For Capturing',
        self::STATUS_QUEUED_CANCEL                  => 'Queued For Cancelling',
        self::STATUS_PARTIALLY_CAPTURED             => 'Partially Captured',
    ];

    /**
     * @inheritdoc
     */
    public function get_entry_point()
    {
        return S2P_SDK_Rest_API::ENTRY_POINT_CARDS;
    }

    /**
     * @inheritdoc
     */
    public function default_functionality() : string
    {
        return self::FUNC_PAYMENTS_LIST;
    }

    /**
     * @inheritdoc
     */
    public function get_notification_types() : ?array
    {
        $payment_notification_obj = new S2P_SDK_Structure_Payment_Response();
        $dispute_notification_obj = new S2P_SDK_Structure_Dispute_Notification();

        return [
            'Payment' => [
                'request_structure' => $payment_notification_obj,
            ],
            'Dispute' => [
                'request_structure' => $dispute_notification_obj,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function finalize(array $call_result, array $params = []) : array
    {
        $return_arr = self::default_finalize_result();

        if (!($call_result = S2P_SDK_Rest_API::validate_call_result($call_result))
            || empty($call_result['response']['func'])) {
            return $return_arr;
        }

        switch ($call_result['response']['func']) {
            case self::FUNC_PAYMENT_INIT:
                if (!empty($call_result['response']['response_array']['payment']['redirecturl'])) {
                    $return_arr['should_redirect'] = true;
                    $return_arr['redirect_to'] = $call_result['response']['response_array']['payment']['redirecturl'];
                }
                break;
        }

        return $return_arr;
    }

    /**
     * @inheritdoc
     */
    public function validate_response(array $response_data) : bool
    {
        $response_data = self::validate_response_data($response_data);

        switch ($response_data['func']) {
            case self::FUNC_PAYMENT_INIT:
            case self::FUNC_PAYMENT_CANCEL:
                // in case we have an error for payments_list we will receive a payment object back
            case self::FUNC_PAYMENTS_LIST:
            case self::FUNC_CAPTURE_PAYMENT:
            case self::FUNC_SPLIT_CAPTURE:
                if (!empty($response_data['response_array']['payment'])) {
                    if (!empty($response_data['response_array']['payment']['status']['reasons'])
                    && is_array($response_data['response_array']['payment']['status']['reasons'])) {
                        $error_msg = '';
                        foreach ($response_data['response_array']['payment']['status']['reasons'] as $reason_arr) {
                            $error_msg .= (!empty($reason_arr['code']) ? $reason_arr['code'].' - ' : '')
                                          .(!empty($reason_arr['info']) ? $reason_arr['info'] : '');
                        }

                        if ($error_msg !== '') {
                            $error_msg = self::s2p_t('Returned by server: %s', $error_msg);
                            $this->set_error(self::ERR_REASON_CODE, $error_msg);

                            return false;
                        }
                    }

                    if (empty($response_data['request_http_code'])
                     || !in_array($response_data['request_http_code'], S2P_SDK_Rest_API_Codes::success_codes(), true)) {
                        $this->set_error(self::ERR_EMPTY_ID, self::s2p_t('API call failed with error code %s.', $response_data['request_http_code']));

                        return false;
                    }

                    if (empty($response_data['response_array']['payment']['id'])) {
                        $this->set_error(self::ERR_EMPTY_ID, self::s2p_t('Payment ID is empty.'));

                        return false;
                    }

                    if ($response_data['func'] === self::FUNC_PAYMENT_CANCEL
                        && (int)($response_data['response_array']['payment']['status']['id'] ?? 0) !== self::STATUS_CANCELLED) {
                        $this->set_error(self::ERR_EMPTY_ID, self::s2p_t('Payment not cancelled.'));

                        return false;
                    }
                }
                break;

            case self::FUNC_REFUNDS_LIST:
            case self::FUNC_REFUND_INIT:
            case self::FUNC_SPLIT_REFUND:
            case self::FUNC_REFUND_DETAILS:
                if (!empty($response_data['response_array']['refund'])) {
                    if (!empty($response_data['response_array']['refund']['status']['reasons'])
                    && is_array($response_data['response_array']['refund']['status']['reasons'])) {
                        $error_msg = '';
                        foreach ($response_data['response_array']['refund']['status']['reasons'] as $reason_arr) {
                            $error_msg .= (!empty($reason_arr['code']) ? $reason_arr['code'].' - ' : '')
                                          .(!empty($reason_arr['info']) ? $reason_arr['info'] : '');
                        }

                        if ($error_msg !== '') {
                            $error_msg = self::s2p_t('Returned by server: %s', $error_msg);
                            $this->set_error(self::ERR_REASON_CODE, $error_msg);

                            return false;
                        }
                    }

                    if (empty($response_data['response_array']['refund']['id'])) {
                        $this->set_error(self::ERR_EMPTY_ID, self::s2p_t('Refund ID is empty.'));

                        return false;
                    }
                }
                break;

            case self::FUNC_PAYOUT_INIT:
            case self::FUNC_PAYOUT_STATUS:
            case self::FUNC_PAYOUT_DETAILS:
                if (!empty($response_data['response_array']['payout'])) {
                    if (!empty($response_data['response_array']['payout']['status']['reasons'])
                    && is_array($response_data['response_array']['payout']['status']['reasons'])) {
                        $error_msg = '';
                        foreach ($response_data['response_array']['payout']['status']['reasons'] as $reason_arr) {
                            $error_msg .= (!empty($reason_arr['code']) ? $reason_arr['code'].' - ' : '')
                                          .(!empty($reason_arr['info']) ? $reason_arr['info'] : '');
                        }

                        if ($error_msg !== '') {
                            $error_msg = self::s2p_t('Returned by server: %s', $error_msg);
                            $this->set_error(self::ERR_REASON_CODE, $error_msg);

                            return false;
                        }
                    }

                    if (empty($response_data['response_array']['payout']['id'])) {
                        $this->set_error(self::ERR_EMPTY_ID, self::s2p_t('Payout ID is empty.'));

                        return false;
                    }
                }
                break;

            case self::FUNC_CARDAUTH_INIT:
            case self::FUNC_CARDAUTH_DETAILS:
            case self::FUNC_CARDAUTH_CANCEL:
                if (!empty($response_data['response_array']['cardauthentication'])) {
                    if (!empty($response_data['response_array']['cardauthentication']['status']['reasons'])
                    && is_array($response_data['response_array']['cardauthentication']['status']['reasons'])) {
                        $error_msg = '';
                        foreach ($response_data['response_array']['cardauthentication']['status']['reasons'] as $reason_arr) {
                            $error_msg .= (!empty($reason_arr['code']) ? $reason_arr['code'].' - ' : '')
                                          .(!empty($reason_arr['info']) ? $reason_arr['info'] : '');
                        }

                        if ($error_msg !== '') {
                            $error_msg = self::s2p_t('Returned by server: %s', $error_msg);
                            $this->set_error(self::ERR_REASON_CODE, $error_msg);

                            return false;
                        }
                    }

                    if (empty($response_data['response_array']['cardauthentication']['credicardtoken']['value'])) {
                        $this->set_error(self::ERR_EMPTY_ID, self::s2p_t('Credit card token is empty.'));

                        return false;
                    }
                }
                break;
        }

        return true;
    }

    public function get_method_details() : array
    {
        return [
            'method'            => 'cards',
            'name'              => self::s2p_t('Cards'),
            'short_description' => self::s2p_t('This method manages card payments'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_functionalities() : array
    {
        $card_payment_request_obj = new S2P_SDK_Structure_Card_Payment_Request();
        $card_split_capture_request_obj = new S2P_SDK_Structure_Card_Split_Capture_Request();
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

        return [
            self::FUNC_PAYMENT_INIT => [
                'name'        => self::s2p_t('Initiate a Card Payment'),
                'url_suffix'  => '/v1/payments/',
                'http_method' => 'POST',

                'mandatory_in_request' => [
                    'Payment' => [
                        'MerchantTransactionID' => '',
                        'Amount'                => 0,
                        'Currency'              => '',
                        'ReturnURL'             => '',
                    ],
                ],

                'request_structure' => $card_payment_request_obj,

                'hide_in_request' => [
                    'Payment' => [
                        'Customer' => [
                            'InputDateTime' => '',
                        ],
                    ],
                ],

                'mandatory_in_response' => [
                    'payment' => [],
                ],

                'response_structure' => $payment_response_obj,

                'mandatory_in_error' => [
                    'payment' => [],
                ],

                'error_structure' => $payment_response_obj,
            ],

            self::FUNC_PAYMENT_DETAILS => [
                'name'        => self::s2p_t('Card Payment Details'),
                'url_suffix'  => '/v1/payments/{*ID*}/',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'response_structure' => $payment_response_obj,
            ],

            self::FUNC_PAYMENT_STATUS => [
                'name'        => self::s2p_t('Card Payment Status'),
                'url_suffix'  => '/v1/payments/{*ID*}/statuss',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'response_structure' => $payment_response_obj,
            ],

            self::FUNC_CAPTURE_PAYMENT => [
                'name'        => self::s2p_t('Capture Card Payment'),
                'url_suffix'  => '/v1/payments/{*ID*}/capture',
                'http_method' => 'POST',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'response_structure' => $payment_response_obj,

                'mandatory_in_error' => [
                    'payment' => [],
                ],

                'error_structure' => $payment_response_obj,
            ],

            self::FUNC_SPLIT_CAPTURE => [
                'name'        => self::s2p_t('Capture Split Payment'),
                'url_suffix'  => '/v1/payments/{*ID*}/capture',
                'http_method' => 'POST',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_request' => [
                    'Payment' => [
                        'Split' => [
                            'ID'     => '',
                            'Amount' => 0,
                        ],
                    ],
                ],

                'request_structure' => $card_split_capture_request_obj,

                'mandatory_in_response' => [
                    'payment' => [],
                ],

                'response_structure' => $payment_response_obj,

                'mandatory_in_error' => [
                    'payment' => [],
                ],

                'error_structure' => $payment_response_obj,
            ],

            self::FUNC_PAYMENT_CANCEL => [
                'name'        => self::s2p_t('Cancel a Card Payment'),
                'url_suffix'  => '/v1/payments/{*ID*}/cancel/',
                'http_method' => 'POST',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'response_structure' => $payment_response_obj,

                'mandatory_in_error' => [
                    'payment' => [],
                ],

                'error_structure' => $payment_response_obj,
            ],

            self::FUNC_PAYMENTS_LIST => [
                'name'        => self::s2p_t('List Card Payments'),
                'url_suffix'  => '/v1/payments/',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'limit',
                        'display_name' => self::s2p_t('Rows limit'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default'      => 0,
                        'mandatory'    => false,
                    ],
                    [
                        'name'          => 'start_date',
                        'external_name' => 'startDate',
                        'display_name'  => self::s2p_t('Interval starting date'),
                        'type'          => S2P_SDK_Scope_Variable::TYPE_DATETIME,
                        'default'       => '',
                        'mandatory'     => false,
                    ],
                    [
                        'name'          => 'end_date',
                        'external_name' => 'endDate',
                        'display_name'  => self::s2p_t('Interval ending date'),
                        'type'          => S2P_SDK_Scope_Variable::TYPE_DATETIME,
                        'default'       => '',
                        'mandatory'     => false,
                    ],
                    [
                        'name'         => 'country',
                        'type'         => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'display_name' => self::s2p_t('Country'),
                        'default'      => '',
                        'mandatory'    => false,
                        'value_source' => S2P_SDK_Values_Source::TYPE_COUNTRY,
                    ],
                    [
                        'name'         => 'currency',
                        'type'         => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'display_name' => self::s2p_t('Currency'),
                        'default'      => '',
                        'mandatory'    => false,
                        'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
                    ],
                    [
                        'name'          => 'minimum_amount',
                        'external_name' => 'minimumAmount',
                        'display_name'  => self::s2p_t('Minimum amount'),
                        'type'          => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default'       => 0,
                        'mandatory'     => false,
                    ],
                    [
                        'name'          => 'maximum_amount',
                        'external_name' => 'maximumAmount',
                        'display_name'  => self::s2p_t('Maximum amount'),
                        'type'          => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default'       => 0,
                        'mandatory'     => false,
                    ],
                    [
                        'name'          => 'merchant_transaction_id',
                        'external_name' => 'merchantTransactionID',
                        'display_name'  => self::s2p_t('Merchant transaction ID'),
                        'type'          => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default'       => '',
                        'mandatory'     => false,
                    ],
                ],

                'mandatory_in_response' => [
                    'payments' => [],
                ],

                'response_structure' => $payment_response_list_obj,

                'mandatory_in_error' => [
                    'payment' => [],
                ],

                'error_structure' => $payment_response_obj,
            ],

            self::FUNC_REFUND_INIT => [
                'name'        => self::s2p_t('Initiate a Card Payment Refund'),
                'url_suffix'  => '/v1/payments/{*ID*}/refunds',
                'http_method' => 'POST',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_request' => [
                    'Refund' => [
                        'MerchantTransactionID' => '',
                        'Amount'                => 0,
                    ],
                ],

                'request_structure' => $refund_request_obj,

                'mandatory_in_response' => [
                    'refund' => [],
                ],

                'response_structure' => $refund_response_obj,

                'mandatory_in_error' => [
                    'refund' => [],
                ],

                'error_structure' => $refund_response_obj,
            ],

            self::FUNC_SPLIT_REFUND => [
                'name'        => self::s2p_t('Initiate a Card Split Refund'),
                'url_suffix'  => '/v1/payments/{*ID*}/splits/{*SPLIT_ID*}/refunds',
                'http_method' => 'POST',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                    [
                        'name'         => 'split_id',
                        'display_name' => self::s2p_t('Payment Split ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_request' => [
                    'Refund' => [
                        'CaptureID'             => 0,
                        'MerchantTransactionID' => '',
                        'Amount'                => 0,
                    ],
                ],

                'request_structure' => $refund_request_obj,

                'mandatory_in_response' => [
                    'refund' => [],
                ],

                'response_structure' => $refund_response_obj,

                'mandatory_in_error' => [
                    'refund' => [],
                ],

                'error_structure' => $refund_response_obj,
            ],

            self::FUNC_REFUNDS_LIST => [
                'name'        => self::s2p_t('List Card Payment Refunds'),
                'url_suffix'  => '/v1/payments/{*ID*}/refunds',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'refunds' => [],
                ],

                'response_structure' => $refund_response_list_obj,

                'mandatory_in_error' => [
                    'refund' => [],
                ],

                'error_structure' => $refund_response_obj,
            ],

            self::FUNC_REFUND_DETAILS => [
                'name'        => self::s2p_t('Card Payment Refund Details'),
                'url_suffix'  => '/v1/payments/{*PAYMENT_ID*}/refunds/{*ID*}',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'payment_id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Refund ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'refund' => [],
                ],

                'response_structure' => $refund_response_obj,

                'mandatory_in_error' => [
                    'refund' => [],
                ],

                'error_structure' => $refund_response_obj,
            ],

            self::FUNC_REFUND_STATUS => [
                'name'        => self::s2p_t('Get Cart Payment Refund Status'),
                'url_suffix'  => '/v1/refunds/{*ID*}/status',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Refund ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'refund' => [],
                ],

                'response_structure' => $refund_response_obj,

                'mandatory_in_error' => [
                    'refund' => [],
                ],

                'error_structure' => $refund_response_obj,
            ],

            self::FUNC_PAYOUT_INIT => [
                'name'        => self::s2p_t('Initiate a Payout'),
                'url_suffix'  => '/v1/payouts',
                'http_method' => 'POST',

                'mandatory_in_request' => [
                    'Payout' => [
                        'MerchantTransactionID' => '',
                        'Amount'                => 0,
                        'Currency'              => '',
                    ],
                ],

                'request_structure' => $payout_request_obj,

                'hide_in_request' => [
                    'Payout' => [
                        'Customer' => [
                            'InputDateTime' => '',
                        ],
                    ],
                ],

                'mandatory_in_response' => [
                    'payout' => [],
                ],

                'response_structure' => $payout_response_obj,

                'mandatory_in_error' => [
                    'payout' => [],
                ],

                'error_structure' => $payout_response_obj,
            ],

            self::FUNC_PAYOUT_LIST => [
                'name'        => self::s2p_t('List Payouts'),
                'url_suffix'  => '/v1/payouts',
                'http_method' => 'GET',

                'mandatory_in_response' => [
                    'payouts' => [],
                ],

                'response_structure' => $payout_response_list_obj,

                'mandatory_in_error' => [
                    'payout' => [],
                ],

                'error_structure' => $payout_response_obj,
            ],

            self::FUNC_PAYOUT_DETAILS => [
                'name'        => self::s2p_t('Payout Details'),
                'url_suffix'  => '/v1/payouts/{*ID*}',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payout ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'payout' => [],
                ],

                'response_structure' => $payout_response_obj,

                'mandatory_in_error' => [
                    'payout' => [],
                ],

                'error_structure' => $payout_response_obj,
            ],

            self::FUNC_PAYOUT_STATUS => [
                'name'        => self::s2p_t('Get Payout Status'),
                'url_suffix'  => '/v1/payouts/{*ID*}/status',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payout ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'payout' => [],
                ],

                'response_structure' => $payout_response_obj,

                'mandatory_in_error' => [
                    'payout' => [],
                ],

                'error_structure' => $payout_response_obj,
            ],

            self::FUNC_CARDAUTH_INIT => [
                'name'        => self::s2p_t('Initiate a Card Authentication'),
                'url_suffix'  => '/v1/card/authenticate',
                'http_method' => 'POST',

                'mandatory_in_request' => [
                    'CardAuthentication' => [
                        'Card' => [
                            'Number' => '',
                        ],
                    ],
                ],

                'request_structure' => $card_auth_request_obj,

                'mandatory_in_response' => [
                    'card_authentication' => [],
                ],

                'response_structure' => $card_auth_response_obj,

                'mandatory_in_error' => [
                    'card_authentication' => [],
                ],

                'error_structure' => $card_auth_response_obj,
            ],

            self::FUNC_CARDAUTH_DETAILS => [
                'name'        => self::s2p_t('Get Card Authentication Details'),
                'url_suffix'  => '/v1/card/token/{*TOKEN*}',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'token',
                        'display_name' => self::s2p_t('Card Authentication Token'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default'      => '',
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'card_authentication' => [],
                ],

                'response_structure' => $card_auth_response_obj,

                'mandatory_in_error' => [
                    'card_authentication' => [],
                ],

                'error_structure' => $card_auth_response_obj,
            ],

            self::FUNC_CARDAUTH_CANCEL => [
                'name'        => self::s2p_t('Get Cart Payment Refund Status'),
                'url_suffix'  => '/v1/card/token/{*TOKEN*}/cancel',
                'http_method' => 'POST',

                'get_variables' => [
                    [
                        'name'         => 'token',
                        'display_name' => self::s2p_t('Card Authentication Token'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default'      => '',
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'card_authentication' => [],
                ],

                'response_structure' => $card_auth_response_obj,

                'mandatory_in_error' => [
                    'card_authentication' => [],
                ],

                'error_structure' => $card_auth_response_obj,
            ],

            self::FUNC_CAPUTES_LIST => [
                'name'        => self::s2p_t('List Card Payment Captures'),
                'url_suffix'  => '/v1/payments/{*ID*}/captures',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'captures' => [],
                ],

                'response_structure' => $capture_response_list_obj,
            ],

            self::FUNC_CAPTURE_DETAILS => [
                'name'        => self::s2p_t('Payment Card Capture Details'),
                'url_suffix'  => '/v1/payments/{*PAYMENT_ID*}/captures/{*ID*}',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'payment_id',
                        'display_name' => self::s2p_t('Payment ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Capture ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'response_structure' => $capture_response_obj,
            ],
        ];
    }

    public static function get_statuses()
    {
        return self::$STATUSES_ARR;
    }

    public static function valid_status($status)
    {
        if (empty($status)
         || !($statuses_arr = self::get_statuses()) || empty($statuses_arr[$status])) {
            return false;
        }

        return $statuses_arr[$status];
    }
}
