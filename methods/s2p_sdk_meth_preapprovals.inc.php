<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_preapproval_request.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_preapproval_response.inc.php' );
include_once( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' );

if( !defined( 'S2P_SDK_METH_PREAPPROVAL_INIT' ) )
    define( 'S2P_SDK_METH_PREAPPROVAL_INIT', 'preapproval_init' );

class S2P_SDK_Meth_Preapprovals extends S2P_SDK_Method
{
    const ERR_REASON_CODE = 300, ERR_EMPTY_ID = 301;

    const FUNC_INIT_PREAPPROVAL = S2P_SDK_METH_PREAPPROVAL_INIT;

    const STATUS_PENDING = 1, STATUS_OPEN = 2, STATUS_CLOSEDBYCUSTOMER = 4;

    const METH_MERCADOPAGO = 46, METH_PAYWITHMYBANK = 58, METH_CARDS = 69, METH_KLARNAINVOICE = 75, METH_QIWIWALLET = 1003;

    private static $RECURRING_METHODS = array(
        self::METH_MERCADOPAGO => array(
            'title' => 'MercadoPago',
        ),
        self::METH_PAYWITHMYBANK => array(
            'title' => 'PayWithMyBank',
        ),
        self::METH_CARDS => array(
            'title' => 'Cards',
        ),
        self::METH_KLARNAINVOICE => array(
            'title' => 'Klarna Invoice',
        ),
        self::METH_QIWIWALLET => array(
            'title' => 'QIWI Wallet',
        ),
    );

    public static function get_recurring_methods()
    {
        return self::$RECURRING_METHODS;
    }

    public static function valid_recurring_method( $meth )
    {
        $meth = intval( $meth );
        if( !($recurring_methods = self::get_recurring_methods()) or empty( $recurring_methods[$meth] ) )
            return false;

        return $recurring_methods[$meth];
    }

    public function default_functionality()
    {
        return self::FUNC_INIT_PREAPPROVAL;
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

                            if( ! empty( $error_msg ) )
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
        }

        return true;
    }

    public function get_method_details()
    {
        return array(
            'method' => 'preapprovals',
            'name' => self::s2p_t( 'Preapprovals' ),
            'short_description' => self::s2p_t( 'This method manages preapprovals used in recurring payments' ),
        );
    }

    public function get_functionalities()
    {
        $preapproval_request_obj = new S2P_SDK_Structure_Preapproval_Request();
        $preapproval_response_obj = new S2P_SDK_Structure_Preapproval_Response();

        return array(

            self::FUNC_INIT_PREAPPROVAL => array(
                'name' => self::s2p_t( 'Initialize a Preapproval' ),
                'url_suffix' => '/v1/preapprovals/',
                'http_method' => 'POST',

                'mandatory_in_request' => array(
                    'Preapproval' => array(
                        'MerchantPreapprovalID' => '',
                        'Description' => '',
                        'ReturnURL' => '',
                        'MethodID' => 0,
                    ),
                ),

                'request_structure' => $preapproval_request_obj,

                'mandatory_in_response' => array(
                    'preapproval' => array(),
                ),

                'response_structure' => $preapproval_response_obj,
            ),
       );
    }
}