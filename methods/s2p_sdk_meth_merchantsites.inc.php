<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_merchantsite.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_merchantsite_list.inc.php' );
include_once( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' );

if( !defined( 'S2P_SDK_METH_MSITES_LIST_ALL' ) )
    define( 'S2P_SDK_METH_MSITES_LIST_ALL', 'list_all' );
if( !defined( 'S2P_SDK_METH_MSITES_DETAILS' ) )
    define( 'S2P_SDK_METH_MSITES_DETAILS', 'site_details' );

class S2P_SDK_Meth_Merchantsites extends S2P_SDK_Method
{
    const FUNC_LIST_ALL = S2P_SDK_METH_MSITES_LIST_ALL, FUNC_SITE_DETAILS = S2P_SDK_METH_MSITES_DETAILS;

    public function default_functionality()
    {
        return self::FUNC_LIST_ALL;
    }

    public function get_functionalities()
    {
        $merchantsite_obj = new S2P_SDK_Structure_Merchantsite();
        $merchantsite_list_obj = new S2P_SDK_Structure_Merchantsite_List();

        return array(

            self::FUNC_LIST_ALL => array(
                'name' => self::s2p_t( 'List Merchat Sites' ),
                'url_suffix' => '/v1/merchantsites/',

                'mandatory_in_response' => array(
                    'merchantsites' => array(),
                ),

                'response_structure' => $merchantsite_list_obj,
            ),

            self::FUNC_SITE_DETAILS => array(
                'name' => self::s2p_t( 'Merchant Site Details' ),
                'url_suffix' => '/v1/merchantsites/',
                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'merchantsite' => array(),
                ),

                'response_structure' => $merchantsite_obj,
            ),
       );
    }
}