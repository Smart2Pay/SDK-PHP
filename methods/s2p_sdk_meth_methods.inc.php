<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_STRUCTURES' ) or !defined( 'S2P_SDK_DIR_CLASSES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_method.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_method_list.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_request.inc.php' );
include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_method.inc.php' );
include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_values_source.inc.php' );

if( !defined( 'S2P_SDK_METH_METHODS_LIST_ALL' ) )
    define( 'S2P_SDK_METH_METHODS_LIST_ALL', 'list_all' );
if( !defined( 'S2P_SDK_METH_METHODS_DETAILS' ) )
    define( 'S2P_SDK_METH_METHODS_DETAILS', 'method_details' );
if( !defined( 'S2P_SDK_METH_METHODS_FOR_COUNTRY' ) )
    define( 'S2P_SDK_METH_METHODS_FOR_COUNTRY', 'for_country' );
if( !defined( 'S2P_SDK_METH_METHODS_ASSIGNED' ) )
    define( 'S2P_SDK_METH_METHODS_ASSIGNED', 'assigned_methods' );
if( !defined( 'S2P_SDK_METH_METHODS_ASSIGNED_COUNTRY' ) )
    define( 'S2P_SDK_METH_METHODS_ASSIGNED_COUNTRY', 'assigned_for_country' );

class S2P_SDK_Meth_Methods extends S2P_SDK_Method
{
    const FUNC_LIST_ALL = S2P_SDK_METH_METHODS_LIST_ALL, FUNC_METHOD_DETAILS = S2P_SDK_METH_METHODS_DETAILS, FUNC_LIST_COUNTRY = S2P_SDK_METH_METHODS_FOR_COUNTRY,
          FUNC_ASSIGNED = S2P_SDK_METH_METHODS_ASSIGNED, FUNC_ASSIGNED_COUNTRY = S2P_SDK_METH_METHODS_ASSIGNED_COUNTRY;

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
        return self::FUNC_LIST_ALL;
    }

    /**
     * Extracts custom validator for specific payment methods. Details about validator comes from server...
     *
     * @param array $validator_arr
     * @param false|array $payment_request_arr Payment request array obtained from \S2P_SDK\S2P_SDK_Structure_Payment_Request
     *
     * @return false|array Returns a payment request node transformed from validator source
     */
    public static function extract_method_validator( $validator_arr, $payment_request_arr = false )
    {
        if( empty( $validator_arr ) or !is_array( $validator_arr )
         or empty( $validator_arr['source'] ) )
            return false;

        if( empty( $payment_request_arr ) or !is_array( $payment_request_arr ) )
        {
            $pay_request_obj = new S2P_SDK_Structure_Payment_Request();
            $variable_obj = new S2P_SDK_Scope_Variable( $pay_request_obj->get_definition() );

            $payment_request_arr = $variable_obj->nullify( null, array( 'check_external_names' => false, 'nullify_full_object' => true ) );
        }

        $return_arr = array();
        $return_arr['sources'] = array();
        $return_arr['regexp'] = (!empty( $validator_arr['regex'] )?$validator_arr['regex']:false);
        $return_arr['required'] = (!empty( $validator_arr['required'] )?true:false);

        $sources_arr = explode( ' ', $validator_arr['source'] );
        foreach( $sources_arr as $source )
        {
            $source = trim( $source );
            if( $source == ''
             // hardcoded to remove language from custom validators
             or $source == 'Language'
             // hardcoded to remove currency from custom validators as currency is mandatory in payment request
             or $source == 'Currency' )
                continue;

            $parts_arr = explode( '.', $source );
            $node_arr = $payment_request_arr;
            $path_found = true;
            $return_node_arr = &$return_arr['sources'];
            while( !empty( $parts_arr ) and is_array( $parts_arr ) and ($key = array_shift( $parts_arr )) )
            {
                if( !is_array( $node_arr )
                 or !array_key_exists( $key, $node_arr ) )
                {
                    $path_found = false;
                    break;
                }

                if( !array_key_exists( $key, $return_node_arr ) )
                    $return_node_arr[$key] = array();

                $return_node_arr = &$return_node_arr[$key];

                if( empty( $parts_arr ) )
                    break;

                $node_arr = $node_arr[$key];
            }

            if( is_array( $return_node_arr ) and empty( $return_node_arr ) )
                $return_node_arr = '';

            if( empty( $path_found ) )
                return false;
        }

        return $return_arr;
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
            case self::FUNC_METHOD_DETAILS:
                if( !empty( $call_result['response']['response_array']['method'] ) )
                {
                    $return_arr['custom_validators'] = array();
                    $return_arr['custom_validators']['payment'] = array();
                    $return_arr['custom_validators']['recurrent'] = array();

                    $pay_request_obj = new S2P_SDK_Structure_Payment_Request();
                    $variable_obj = new S2P_SDK_Scope_Variable( $pay_request_obj->get_definition() );

                    $payment_request_arr = $variable_obj->nullify( null, array( 'check_external_names' => false, 'nullify_full_object' => true ) );

                    $we_have_validators = false;
                    if( !empty( $call_result['response']['response_array']['method']['validatorspayin'] )
                    and is_array( $call_result['response']['response_array']['method']['validatorspayin'] ) )
                    {
                        $custom_validators = array();
                        foreach( $call_result['response']['response_array']['method']['validatorspayin'] as $validator_arr )
                        {
                            if( ($custom_validator = self::extract_method_validator( $validator_arr, $payment_request_arr )) )
                            {
                                if( !($transform_result = $variable_obj->transform_keys( array( 'Payment' => $custom_validator['sources'] ), null, array( 'check_external_names' => true ) ))
                                 or !is_array( $transform_result )
                                 or empty( $transform_result['payment'] ) or !is_array( $transform_result['payment'] ) )
                                    continue;

                                $custom_validator['sources'] = $transform_result['payment'];

                                $custom_validators[] = $custom_validator;
                            }
                        }

                        if( !empty( $custom_validators ) )
                        {
                            $return_arr['custom_validators']['payment'] = $custom_validators;
                            $we_have_validators = true;
                        }
                    }

                    if( !empty( $call_result['response']['response_array']['method']['validatorsrecurrent'] )
                    and is_array( $call_result['response']['response_array']['method']['validatorsrecurrent'] ) )
                    {
                        $custom_validators = array();
                        foreach( $call_result['response']['response_array']['method']['validatorsrecurrent'] as $validator_arr )
                        {
                            if( ($custom_validator = self::extract_method_validator( $validator_arr, $payment_request_arr )) )
                            {
                                if( !($transform_result = $variable_obj->transform_keys( array( 'Payment' => $custom_validator['sources'] ), null, array( 'check_external_names' => true ) ))
                                 or !is_array( $transform_result )
                                 or empty( $transform_result['payment'] ) or !is_array( $transform_result['payment'] ) )
                                    continue;

                                $custom_validator['sources'] = $transform_result['payment'];

                                $custom_validators[] = $custom_validator;
                            }
                        }

                        if( !empty( $custom_validators ) )
                        {
                            $return_arr['custom_validators']['recurrent'] = $custom_validators;
                            $we_have_validators = true;
                        }
                    }

                    if( empty( $we_have_validators ) )
                        $return_arr['custom_validators'] = false;
                }
                break;
        }

        return $return_arr;
    }

    public function get_method_details()
    {
        return array(
            'method' => 'methods',
            'name' => self::s2p_t( 'Payment methods' ),
            'short_description' => self::s2p_t( 'This method helps you manage payment methods' ),
        );
    }

    public function get_functionalities()
    {
        $method_obj = new S2P_SDK_Structure_Method();
        $method_list_obj = new S2P_SDK_Structure_Method_List();

        return array(

            self::FUNC_LIST_ALL => array(
                'name' => self::s2p_t( 'List all methods' ),
                'url_suffix' => '/v1/methods/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'additional_details',
                        'external_name' => 'additionalDetails',
                        'display_name' => self::s2p_t( 'Additional details' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_BOOL,
                        'default' => false,
                        'mandatory' => false,
                    ),
                ),

                'mandatory_in_response' => array(
                    'methods' => array(),
                ),

                'response_structure' => $method_list_obj,
            ),

            self::FUNC_METHOD_DETAILS => array(
                'name' => self::s2p_t( 'Get method details' ),
                'url_suffix' => '/v1/methods/{*ID*}/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'display_name' => self::s2p_t( 'Method ID' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                        'value_source' => S2P_SDK_Values_Source::TYPE_METHODS,
                    ),
                ),

                'mandatory_in_response' => array(
                    'method' => array(),
                ),

                'response_structure' => $method_obj,
            ),

            self::FUNC_LIST_COUNTRY => array(
                'name' => self::s2p_t( 'Get available methods for specific country' ),
                'url_suffix' => '/v1/methods/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'country',
                        'display_name' => self::s2p_t( 'Country' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => true,
                        'value_source' => S2P_SDK_Values_Source::TYPE_COUNTRY,
                    ),
                ),

                'mandatory_in_response' => array(
                    'methods' => array(),
                ),

                'response_structure' => $method_list_obj,
            ),

            self::FUNC_ASSIGNED => array(
                'name' => self::s2p_t( 'Get merchant\'s assigned methods' ),
                'url_suffix' => '/v1/methods/assigned/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'additional_details',
                        'external_name' => 'additionalDetails',
                        'display_name' => self::s2p_t( 'Additional details' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_BOOL,
                        'default' => false,
                        'mandatory' => false,
                    ),
                ),

                'mandatory_in_response' => array(
                    'methods' => array(),
                ),

                'response_structure' => $method_list_obj,
            ),

            self::FUNC_ASSIGNED_COUNTRY => array(
                'name' => self::s2p_t( 'Get merchant\'s assigned methods for specific country' ),
                'url_suffix' => '/v1/methods/assigned/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'country',
                        'display_name' => self::s2p_t( 'Country' ),
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => true,
                        'value_source' => S2P_SDK_Values_Source::TYPE_COUNTRY,
                    ),
                ),

                'mandatory_in_response' => array(
                    'methods' => array(),
                ),

                'response_structure' => $method_list_obj,
            ),
        );
    }
}
