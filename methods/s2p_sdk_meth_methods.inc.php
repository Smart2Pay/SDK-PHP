<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' );

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

    public function default_functionality()
    {
        return self::FUNC_LIST_ALL;
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
        return array(

            self::FUNC_LIST_ALL => array(
                'name' => self::s2p_t( 'List available methods' ),
                'url_suffix' => '/v1/methods/',
                // TODO: add response structure
                'response_structure' => null,
            ),

            self::FUNC_METHOD_DETAILS => array(
                'name' => self::s2p_t( 'Get method details' ),
                'url_suffix' => '/v1/methods/',
                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                    ),
                ),
                // TODO: add response structure
                'response_structure' => null,
            ),

            self::FUNC_LIST_COUNTRY => array(
                'name' => self::s2p_t( 'Get available methods for specific country' ),
                'url_suffix' => '/v1/methods/',
                'get_variables' => array(
                    array(
                        'name' => 'country',
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => true,
                    ),
                ),
                // TODO: add response structure
                'response_structure' => null,
            ),

            self::FUNC_ASSIGNED => array(
                'name' => self::s2p_t( 'Get merchant\'s assigned methods' ),
                'url_suffix' => '/v1/methods/assigned/',
                // TODO: add response structure
                'response_structure' => null,
            ),

            self::FUNC_ASSIGNED_COUNTRY => array(
                'name' => self::s2p_t( 'Get merchant\'s assigned methods for specific country' ),
                'url_suffix' => '/v1/methods/assigned/',
                'get_variables' => array(
                    array(
                        'name' => 'country',
                        'type' => S2P_SDK_Scope_Variable::TYPE_STRING,
                        'default' => '',
                        'mandatory' => true,
                    ),
                ),
                // TODO: add response structure
                'response_structure' => null,
            ),
        );
    }
}