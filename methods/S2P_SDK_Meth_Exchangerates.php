<?php

namespace S2P_SDK;

class S2P_SDK_Meth_Exchangerates extends S2P_SDK_Method
{
    const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    const FUNC_ECHANGE_RATES = 'exchangerates';

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
        return self::FUNC_ECHANGE_RATES;
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
            case self::FUNC_ECHANGE_RATES:
                if( false and !empty( $response_data['response_array']['merchant'] ) )
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
            'method' => 'echangerates',
            'name' => self::s2p_t( 'Echange rates' ),
            'short_description' => self::s2p_t( 'Get details about exchange rates.' ),
        );
    }

    public function get_functionalities()
    {
        $echangerate_response_obj = new S2P_SDK_Structure_Exchangerate_Response();

        return array(

            self::FUNC_ECHANGE_RATES => array(
                'name' => self::s2p_t( 'Get Exchange Rates' ),
                'url_suffix' => '/v1/exchangerates/{*FROM_CURRENCY*}/{*TO_CURRENCY*}/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'from_currency',
                        'display_name' => self::s2p_t( 'From currency' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => true,
                        'move_in_url' => true,
                        'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
                    ),
                    array(
                        'name' => 'to_currency',
                        'display_name' => self::s2p_t( 'To currency' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => true,
                        'move_in_url' => true,
                        'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
                    ),
                ),

                'mandatory_in_response' => array(
                    'exchangerate' => array(
                        'from' => 0,
                        'to' => 0,
                    ),
                ),

                'response_structure' => $echangerate_response_obj,
            ),

       );
    }
}
