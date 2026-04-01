<?php
namespace S2P_SDK;

class S2P_SDK_Meth_Preapprovals extends S2P_SDK_Method
{
    public const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    public const FUNC_INIT_PREAPPROVAL = 'preapproval_init', FUNC_LIST_ALL = 'list_all', FUNC_DETAILS = 'preapproval_details',
        FUNC_PAYMENTS = 'preapproval_payments', FUNC_CLOSE = 'preapproval_close';

    public const STATUS_PENDING = 1, STATUS_OPEN = 2, STATUS_CLOSEDBYCUSTOMER = 4;

    private static $STATUSES_ARR = [
        self::STATUS_PENDING          => 'Pending',
        self::STATUS_OPEN             => 'Open',
        self::STATUS_CLOSEDBYCUSTOMER => 'Closed By Customer',
    ];

    /**
     * @inheritdoc
     */
    public function get_entry_point()
    {
        return S2P_SDK_Rest_API::ENTRY_POINT_REST;
    }

    /**
     * @inheritdoc
     */
    public function default_functionality() : string
    {
        return self::FUNC_LIST_ALL;
    }

    /**
     * @inheritdoc
     */
    public function get_notification_types() : ?array
    {
        $preapproval_notification_obj = new S2P_SDK_Structure_Preapproval_Response();

        return [
            'Preapproval' => [
                'request_structure' => $preapproval_notification_obj,
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
            case self::FUNC_INIT_PREAPPROVAL:
                if (!empty($call_result['response']['response_array']['preapproval']['redirecturl'])) {
                    $return_arr['should_redirect'] = true;
                    $return_arr['redirect_to'] = $call_result['response']['response_array']['preapproval']['redirecturl'];
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
            case self::FUNC_INIT_PREAPPROVAL:
            case self::FUNC_CLOSE:
                if (!empty($response_data['response_array']['preapproval'])) {
                    if (!empty($response_data['response_array']['preapproval']['status']['reasons'])
                    && is_array($response_data['response_array']['preapproval']['status']['reasons'])) {
                        $error_msg = '';
                        foreach ($response_data['response_array']['preapproval']['status']['reasons'] as $reason_arr) {
                            $error_msg .= (!empty($reason_arr['code']) ? $reason_arr['code'].' - ' : '')
                                          .(!empty($reason_arr['info']) ? $reason_arr['info'] : '');
                        }

                        if ($error_msg !== '') {
                            $error_msg = self::s2p_t('Returned by server: %s', $error_msg);
                            $this->set_error(self::ERR_REASON_CODE, $error_msg);

                            return false;
                        }
                    }

                    if (empty($response_data['response_array']['preapproval']['id'])) {
                        $this->set_error(self::ERR_EMPTY_ID, self::s2p_t('Preapproval ID is empty.'));

                        return false;
                    }

                    if ($response_data['func'] == self::FUNC_CLOSE
                    && isset($response_data['response_array']['preapproval']['status']['id'])
                    && $response_data['response_array']['preapproval']['status']['id'] != self::STATUS_CLOSEDBYCUSTOMER) {
                        $this->set_error(self::ERR_EMPTY_ID, self::s2p_t('Preapproval not closed.'));

                        return false;
                    }
                }
                break;

            case self::FUNC_PAYMENTS:
                if (!empty($response_data['response_array']['payment'])) {
                    if (!empty($response_data['response_array']['payment']['status'])
                    && is_array($response_data['response_array']['payment']['status'])) {
                        if (!empty($response_data['response_array']['payment']['status']['reasons'])
                        && is_array($response_data['response_array']['payment']['status']['reasons'])) {
                            $error_msg = '';
                            foreach ($response_data['response_array']['payment']['status']['reasons'] as $reason_arr) {
                                if (($error_reason = (!empty($reason_arr['code']) ? $reason_arr['code'].' - ' : '').(!empty($reason_arr['info']) ? $reason_arr['info'] : '')) != '') {
                                    $error_msg .= $error_reason;
                                }
                            }

                            if (!empty($error_msg)) {
                                $error_msg = self::s2p_t('Returned by server: %s', $error_msg);
                                $this->set_error(self::ERR_REASON_CODE, $error_msg);

                                return false;
                            }
                        }
                    }
                }
                break;
        }

        return true;
    }

    public function get_method_details() : array
    {
        return [
            'method'            => 'preapprovals',
            'name'              => self::s2p_t('Manage Preapprovals'),
            'short_description' => self::s2p_t('This method manages preapprovals used in recurring payments'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_functionalities() : array
    {
        $preapproval_request_obj = new S2P_SDK_Structure_Preapproval_Request();
        $preapproval_response_obj = new S2P_SDK_Structure_Preapproval_Response();
        $preapproval_response_list_obj = new S2P_SDK_Structure_Preapproval_Response_List();
        $payment_response_obj = new S2P_SDK_Structure_Payment_Response();
        $payment_response_list_obj = new S2P_SDK_Structure_Payment_Response_List();

        return [
            self::FUNC_LIST_ALL => [
                'name'        => self::s2p_t('List Preapprovals'),
                'url_suffix'  => '/v1/preapprovals/',
                'http_method' => 'GET',

                'mandatory_in_response' => [
                    'preapprovals' => [],
                ],

                'response_structure' => $preapproval_response_list_obj,
            ],

            self::FUNC_CLOSE => [
                'name'        => self::s2p_t('Close a Preapproval'),
                'url_suffix'  => '/v1/preapprovals/{*ID*}',
                'http_method' => 'DELETE',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Preapproval ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'preapproval' => [],
                ],

                'response_structure' => $preapproval_response_obj,
            ],

            self::FUNC_DETAILS => [
                'name'        => self::s2p_t('Preapproval Details'),
                'url_suffix'  => '/v1/preapprovals/{*ID*}',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Preapproval ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'preapproval' => [],
                ],

                'response_structure' => $preapproval_response_obj,
            ],

            self::FUNC_PAYMENTS => [
                'name'        => self::s2p_t('Preapproval Payments List'),
                'url_suffix'  => '/v1/preapprovals/{*ID*}/payments',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Preapproval ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_LONG,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
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

            self::FUNC_INIT_PREAPPROVAL => [
                'name'        => self::s2p_t('Initiate a Preapproval'),
                'url_suffix'  => '/v1/preapprovals/',
                'http_method' => 'POST',

                'mandatory_in_request' => [
                    'Preapproval' => [
                        'MerchantPreapprovalID' => '',
                        'Description'           => '',
                        'ReturnURL'             => '',
                        'MethodID'              => 0,
                        'Customer'              => [
                            'Email' => '',
                        ],
                        'BillingAddress' => [
                            'Country' => '',
                        ],
                    ],
                ],

                'hide_in_request' => [
                    'Preapproval' => [
                        'Customer' => [
                            'InputDateTime' => '',
                        ],
                        'Created'   => '',
                        'Signature' => '',
                        'ApiKey'    => '',
                        'Details'   => '',
                    ],
                ],

                'request_structure' => $preapproval_request_obj,

                'mandatory_in_response' => [
                    'preapproval' => [],
                ],

                'response_structure' => $preapproval_response_obj,

                'mandatory_in_error' => [
                    'preapproval' => [],
                ],

                'error_structure' => $preapproval_response_obj,
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
