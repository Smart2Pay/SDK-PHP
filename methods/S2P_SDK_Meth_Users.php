<?php
namespace S2P_SDK;

class S2P_SDK_Meth_Users extends S2P_SDK_Method
{
    public const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    public const FUNC_CREATE = 'create', FUNC_EDIT = 'edit';

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
        return self::FUNC_CREATE;
    }

    /**
     * @inheritdoc
     */
    public function validate_response(array $response_data) : bool
    {
        $response_data = self::validate_response_data($response_data);

        switch ($response_data['func']) {
            case self::FUNC_EDIT:
            case self::FUNC_CREATE:
                if (!empty($response_data['response_array']['user'])) {
                    if (!empty($response_data['response_array']['user']['details'])
                     && !empty($response_data['response_array']['user']['details']['reasons'])
                     && is_array($response_data['response_array']['user']['details']['reasons'])) {
                        $error_msg = '';
                        foreach ($response_data['response_array']['user']['details']['reasons'] as $reason_arr) {
                            if (($error_reason = (!empty($reason_arr['code']) ? $reason_arr['code'].' - ' : '')
                                                 .(!empty($reason_arr['info']) ? $reason_arr['info'] : '')) !== '') {
                                $error_msg .= $error_reason;
                            }
                        }

                        if (!empty($error_msg)) {
                            $error_msg = self::s2p_t('Returned by server: %s', $error_msg);
                            $this->set_error(self::ERR_REASON_CODE, $error_msg);

                            return false;
                        }
                    }

                    if (empty($response_data['response_array']['user']['id'])) {
                        $this->set_error(self::ERR_EMPTY_ID, self::s2p_t('User ID is empty.'));

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
            'method'            => 'users',
            'name'              => self::s2p_t('User Accounts'),
            'short_description' => self::s2p_t('Create user accounts under merchant account'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_functionalities() : array
    {
        $user_request_obj = new S2P_SDK_Structure_User_Request();
        $user_response_obj = new S2P_SDK_Structure_User_Response();

        return [
            self::FUNC_CREATE => [
                'name'        => self::s2p_t('Create User Account'),
                'url_suffix'  => '/v1/users/',
                'http_method' => 'POST',

                'mandatory_in_request' => [
                    'User' => [
                        'Name'  => '',
                        'Email' => '',
                    ],
                ],

                'request_structure' => $user_request_obj,

                'mandatory_in_response' => [
                    'user' => [
                        'id' => 0,
                    ],
                ],

                'response_structure' => $user_response_obj,

                'error_structure' => $user_response_obj,
            ],

            self::FUNC_EDIT => [
                'name'        => self::s2p_t('Edit User Account'),
                'url_suffix'  => '/v1/users/{*ID*}/',
                'http_method' => 'PATCH',

                'get_variables' => [
                    [
                        'name'         => 'id',
                        'display_name' => self::s2p_t('User Account ID'),
                        'type'         => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default'      => 0,
                        'mandatory'    => true,
                        'move_in_url'  => true,
                    ],
                ],

                'hide_in_request' => [
                    'User' => [
                        'Name'   => '',
                        'SiteID' => 0,
                        'RoleID' => 0,
                    ],
                ],

                'request_structure' => $user_request_obj,

                'mandatory_in_response' => [
                    'user' => [
                        'id' => 0,
                    ],
                ],

                'response_structure' => $user_response_obj,

                'error_structure' => $user_response_obj,
            ],
        ];
    }
}
