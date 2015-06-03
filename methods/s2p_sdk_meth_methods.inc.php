<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' );

class S2P_SDK_Meth_Methods extends S2P_SDK_Method
{
    const FUNC_LIST_ALL = 1, FUNC_METHOD_DETAILS = 2, FUNC_LIST_COUNTRY = 3, FUNC_ASSIGNED = 4, FUNC_ASSIGNED_COUNTRY = 5;

    public function default_functionality()
    {
        return self::FUNC_LIST_ALL;
    }

    public function get_functionalities()
    {
        return array(

            self::FUNC_LIST_ALL => array(
                'name' => 'methods',
                'url_suffix' => '/v1/methods/',
                // TODO: add response structure
                'response_structure' => null,
            ),

            self::FUNC_METHOD_DETAILS => array(
                'name' => 'method_details',
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
                'name' => 'methods_country',
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
                'name' => 'methods_assigned',
                'url_suffix' => '/v1/methods/assigned/',
                // TODO: add response structure
                'response_structure' => null,
            ),

            self::FUNC_ASSIGNED_COUNTRY => array(
                'name' => 'methods_assigned_country',
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