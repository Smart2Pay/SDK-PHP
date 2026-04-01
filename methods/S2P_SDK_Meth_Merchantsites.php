<?php
namespace S2P_SDK;

class S2P_SDK_Meth_Merchantsites extends S2P_SDK_Method
{
    public const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    public const FUNC_LIST_ALL = 'list_all', FUNC_SITE_DETAILS = 'site_details',
        FUNC_SITE_CREATE = 'site_create', FUNC_SITE_EDIT = 'site_edit', FUNC_SITE_DELETE = 'site_delete',
        FUNC_REGEN_APIKEY = 'regen_apikey';

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
    public function get_notification_types() : ?array
    {
        return null;
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
    public function validate_response(array $response_data) : bool
    {
        $response_data = self::validate_response_data($response_data);

        switch ($response_data['func']) {
            case self::FUNC_SITE_CREATE:
            case self::FUNC_SITE_DETAILS:
            case self::FUNC_SITE_EDIT:
            case self::FUNC_SITE_DELETE:
            case self::FUNC_REGEN_APIKEY:
                if (!empty($response_data['response_array']['merchantsite'])) {
                    if (!empty($response_data['response_array']['merchantsite']['details'])
                    && !empty($response_data['response_array']['merchantsite']['details']['reasons'])
                    && is_array($response_data['response_array']['merchantsite']['details']['reasons'])) {
                        $error_msg = '';
                        foreach ($response_data['response_array']['merchantsite']['details']['reasons'] as $reason_arr) {
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

                    if (empty($response_data['response_array']['merchantsite']['id'])) {
                        $this->set_error(self::ERR_EMPTY_ID, self::s2p_t('Merchant site ID is empty.'));

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
            'method'            => 'merchantsites',
            'name'              => self::s2p_t('Merchant Sites'),
            'short_description' => self::s2p_t('This method helps you manage merchant sites'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_functionalities() : array
    {
        $merchantsite_obj = new S2P_SDK_Structure_Merchantsite();
        $merchantsite_list_obj = new S2P_SDK_Structure_Merchantsite_List();

        return [
            self::FUNC_LIST_ALL => [
                'name'        => self::s2p_t('List Merchat Sites'),
                'url_suffix'  => '/v1/merchantsites/',
                'http_method' => 'GET',

                'mandatory_in_response' => [
                    'merchantsites' => [],
                ],

                'response_structure' => $merchantsite_list_obj,
            ],

            self::FUNC_SITE_DETAILS => [
                'name'        => self::s2p_t('Merchant Site Details'),
                'url_suffix'  => '/v1/merchantsites/{*ID*}/',
                'http_method' => 'GET',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Site ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'merchantsite' => [],
                ],

                'response_structure' => $merchantsite_obj,
            ],

            self::FUNC_SITE_CREATE => [
                'name'        => self::s2p_t('Create Merchant Site'),
                'url_suffix'  => '/v1/merchantsites/',
                'http_method' => 'POST',

                'mandatory_in_request' => [
                    'MerchantSite' => [
                        'URL'             => '',
                        'NotificationURL' => '',
                    ],
                ],

                'hide_in_request' => [
                    'MerchantSite' => [
                        'ID'        => '',
                        'Created'   => '',
                        'Signature' => '',
                        'ApiKey'    => '',
                        'Details'   => [
                            'Reasons' => [
                                [
                                    'Code' => '',
                                    'Info' => '',
                                ],
                            ],
                        ],
                    ],
                ],

                'request_structure' => $merchantsite_obj,

                'mandatory_in_response' => [
                    'merchantsite' => [
                        'id' => 0,
                    ],
                ],

                'response_structure' => $merchantsite_obj,

                'error_structure' => $merchantsite_obj,
            ],

            self::FUNC_SITE_EDIT => [
                'name'        => self::s2p_t('Edit Merchant Site'),
                'url_suffix'  => '/v1/merchantsites/{*ID*}/',
                'http_method' => 'PATCH',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Site ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'hide_in_request' => [
                    'MerchantSite' => [
                        'ID'        => '',
                        'Created'   => '',
                        'Signature' => '',
                        'ApiKey'    => '',
                        'Details'   => [
                            'Reasons' => '',
                        ],
                    ],
                ],

                'request_structure' => $merchantsite_obj,

                'mandatory_in_response' => [
                    'merchantsite' => [
                        'id' => 0,
                    ],
                ],

                'hide_in_response' => [
                    'merchantsite' => [
                        'details' => '',
                    ],
                ],

                'response_structure' => $merchantsite_obj,

                'error_structure' => $merchantsite_obj,
            ],

            self::FUNC_SITE_DELETE => [
                'name'        => self::s2p_t('Delete Merchant Site'),
                'url_suffix'  => '/v1/merchantsites/{*ID*}/',
                'http_method' => 'DELETE',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Site ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'merchantsite' => [],
                ],

                'response_structure' => $merchantsite_obj,
            ],

            self::FUNC_REGEN_APIKEY => [
                'name'        => self::s2p_t('Regenerate Merchant Site API Key'),
                'url_suffix'  => '/v1/merchantsites/{*ID*}/regenerateapikey/',
                'http_method' => 'POST',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('Site ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'mandatory_in_response' => [
                    'merchantsite' => [
                        'id' => 0,
                    ],
                ],

                'response_structure' => $merchantsite_obj,

                'error_structure' => $merchantsite_obj,
            ],
        ];
    }
}
