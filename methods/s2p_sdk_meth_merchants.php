<?php

namespace S2P_SDK;

class S2P_SDK_Meth_Merchants extends S2P_SDK_Method
{
    const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    const FUNC_CREATE = 'merchant_create', FUNC_DETAILS = 'merchant_details', FUNC_EDIT = 'merchant_edit', FUNC_DELETE = 'merchant_delete';

    /**
     * Tells which entry point does this method use
     * @return string
     */
    public function get_entry_point()
    {
        return S2P_SDK_Rest_API::ENTRY_POINT_REST;
    }

    /**
     * @inheritdoc
     */
    public function get_notification_types()
    {
        return false;
    }

    public function default_functionality()
    {
        return self::FUNC_DETAILS;
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
            case self::FUNC_DETAILS:
            case self::FUNC_CREATE:
            case self::FUNC_EDIT:
            case self::FUNC_DELETE:
                if( !empty( $response_data['response_array']['merchant'] ) )
                {
                    if( !empty( $response_data['response_array']['merchant']['reasons'] )
                    and is_array( $response_data['response_array']['merchant']['reasons'] ) )
                    {
                        $error_msg = '';
                        foreach( $response_data['response_array']['merchant']['reasons'] as $reason_arr )
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

                    if( empty( $response_data['response_array']['merchant']['id'] ) )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'Merchant ID is empty.' ) );
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
            'method' => 'merchants',
            'name' => self::s2p_t( 'Merchants' ),
            'short_description' => self::s2p_t( 'Manage merchant details.' ),
        );
    }

    public function get_functionalities()
    {
        $merchant_req_obj = new S2P_SDK_Structure_Merchant_Request();
        $merchant_res_obj = new S2P_SDK_Structure_Merchant_Response();
        $merchant_create_request_obj = new S2P_SDK_Structure_Merchant_Create_Request();
        $merchant_create_response_obj = new S2P_SDK_Structure_Merchant_Create_Response();

        return array(

            self::FUNC_DETAILS => array(
                'name' => self::s2p_t( 'Merchant Details' ),
                'url_suffix' => '/v1/merchants/{*ID*}/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Merchant ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'merchant' => array(),
                ),

                'hide_in_response' => array(
                    'merchant' => array(
                        'reasons' => '',
                    ),
                ),

                'response_structure' => $merchant_res_obj,
            ),

            self::FUNC_CREATE => array(
                'name' => self::s2p_t( 'Create Merchant' ),
                'url_suffix' => '/v1/merchants/',
                'http_method' => 'POST',

                'mandatory_in_request' => array(
                    'CompanyName' => '',
                    'CompanyAddress' => '',
                    'Merchant' => array(
                        'Alias' => '',
                    ),
                    'User' => array(
                        'Name' => '',
                        'Email' => '',
                        'Password' => '',
                    ),
                    'MerchantSite' => array(
                        'Alias' => '',
                        'URL' => '',
                        'NotificationURL' => '',
                    ),
                ),

                'request_structure' => $merchant_create_request_obj,

                'mandatory_in_response' => array(
                    'user' => array(
                        'id' => 0,
                    ),
                    'merchant' => array(
                        'id' => 0,
                    ),
                    'merchant_site' => array(
                        'id' => 0,
                    ),
                ),

                'response_structure' => $merchant_create_response_obj,

                'error_structure' => $merchant_res_obj,
            ),

            self::FUNC_EDIT => array(
                'name' => self::s2p_t( 'Edit Merchant' ),
                'url_suffix' => '/v1/merchants/{*ID*}/',
                'http_method' => 'PATCH',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Site ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'request_structure' => $merchant_req_obj,

                'mandatory_in_response' => array(
                    'merchant' => array(
                        'id' => 0,
                    ),
                ),

                'hide_in_response' => array(
                    'merchant' => array(
                        'reasons' => '',
                    ),
                ),

                'response_structure' => $merchant_res_obj,

                'error_structure' => $merchant_res_obj,
            ),

            self::FUNC_DELETE => array(
                'name' => self::s2p_t( 'Delete Merchant' ),
                'url_suffix' => '/v1/merchants/{*ID*}/',
                'http_method' => 'DELETE',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Merchant ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'merchant' => array(),
                ),

                'hide_in_response' => array(
                    'merchant' => array(
                        'reasons' => '',
                    ),
                ),

                'response_structure' => $merchant_res_obj,
            ),
       );
    }
}
