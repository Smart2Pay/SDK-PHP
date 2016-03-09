<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) or !defined( 'S2P_SDK_DIR_METHODS' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_user_response.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_user_request.inc.php' );
include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_method.inc.php' );

class S2P_SDK_Meth_Users extends S2P_SDK_Method
{
    const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    const FUNC_CREATE = 'create';

    /**
     * Tells which entry point does this method use
     * @return string
     */
    public function get_entry_point()
    {
        return S2P_SDK_Rest_API::ENTRY_POINT_REST;
    }

    /**
     * This method defines keywords that can be found in notification body and what structure should be used to extract notification data
     *
     * @param array $notification_data
     *
     * @return array|bool Array with keys that can be found in notification body and data structure details or false if notification is not intended for current method
     */
    public function get_notification_types()
    {
        return false;
    }

    public function default_functionality()
    {
        return self::FUNC_CREATE;
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
            case self::FUNC_CREATE:
                if( !empty( $response_data['response_array']['user'] ) )
                {
                    if( !empty( $response_data['response_array']['user']['details'] )
                    and !empty( $response_data['response_array']['user']['details']['reasons'] )
                    and is_array( $response_data['response_array']['user']['details']['reasons'] ) )
                    {
                        $error_msg = '';
                        foreach( $response_data['response_array']['user']['details']['reasons'] as $reason_arr )
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

                    if( empty( $response_data['response_array']['user']['id'] ) )
                    {
                        $this->set_error( self::ERR_EMPTY_ID, self::s2p_t( 'User ID is empty.' ) );
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
            'method' => 'users',
            'name' => self::s2p_t( 'User Accounts' ),
            'short_description' => self::s2p_t( 'Create user accounts under merchant account' ),
        );
    }

    public function get_functionalities()
    {
        $user_request_obj = new S2P_SDK_Structure_User_Request();
        $user_response_obj = new S2P_SDK_Structure_User_Response();

        return array(

            self::FUNC_CREATE => array(
                'name' => self::s2p_t( 'Create User Account' ),
                'url_suffix' => '/v1/users/',
                'http_method' => 'POST',

                'mandatory_in_request' => array(
                    'User' => array(
                        'Name' => '',
                        'Email' => '',
                    ),
                ),

                'request_structure' => $user_request_obj,

                'mandatory_in_response' => array(
                    'user' => array(
                        'id' => 0,
                    ),
                ),

                'response_structure' => $user_response_obj,

                'error_structure' => $user_response_obj,
            ),
       );
    }
}
