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
if( !defined( 'S2P_SDK_METH_MSITES_CREATE' ) )
    define( 'S2P_SDK_METH_MSITES_CREATE', 'site_create' );
if( !defined( 'S2P_SDK_METH_MSITES_EDIT' ) )
    define( 'S2P_SDK_METH_MSITES_EDIT', 'site_edit' );
if( !defined( 'S2P_SDK_METH_MSITES_REGEN_APIKEY' ) )
    define( 'S2P_SDK_METH_MSITES_REGEN_APIKEY', 'regen_apikey' );
if( !defined( 'S2P_SDK_METH_MSITES_REGEN_SIGNATURE' ) )
    define( 'S2P_SDK_METH_MSITES_REGEN_SIGNATURE', 'regen_signature' );

class S2P_SDK_Meth_Merchantsites extends S2P_SDK_Method
{
    const FUNC_LIST_ALL = S2P_SDK_METH_MSITES_LIST_ALL, FUNC_SITE_DETAILS = S2P_SDK_METH_MSITES_DETAILS,
          FUNC_SITE_CREATE = S2P_SDK_METH_MSITES_CREATE, FUNC_SITE_EDIT = S2P_SDK_METH_MSITES_EDIT,
          FUNC_REGEN_APIKEY = S2P_SDK_METH_MSITES_REGEN_APIKEY, FUNC_REGEN_SIGNATURE = S2P_SDK_METH_MSITES_REGEN_SIGNATURE;

    public function default_functionality()
    {
        return self::FUNC_LIST_ALL;
    }

    public function get_method_details()
    {
        return array(
            'method' => 'merchantsites',
            'name' => self::s2p_t( 'Merchant Sites' ),
            'short_description' => self::s2p_t( 'This method helps you manage merchant sites' ),
        );
    }

    public function get_functionalities()
    {
        $merchantsite_obj = new S2P_SDK_Structure_Merchantsite();
        $merchantsite_list_obj = new S2P_SDK_Structure_Merchantsite_List();

        return array(

            self::FUNC_LIST_ALL => array(
                'name' => self::s2p_t( 'List Merchat Sites' ),
                'url_suffix' => '/v1/merchantsites/',
                'http_method' => 'GET',

                'mandatory_in_response' => array(
                    'merchantsites' => array(),
                ),

                'response_structure' => $merchantsite_list_obj,
            ),

            self::FUNC_SITE_DETAILS => array(
                'name' => self::s2p_t( 'Merchant Site Details' ),
                'url_suffix' => '/v1/merchantsites/{*ID*}/',
                'http_method' => 'GET',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'merchantsite' => array(),
                ),

                'response_structure' => $merchantsite_obj,
            ),

            self::FUNC_SITE_CREATE => array(
                'name' => self::s2p_t( 'Create Merchant Site' ),
                'url_suffix' => '/v1/merchantsites/',
                'http_method' => 'POST',

                'mandatory_in_request' => array(
                    'MerchantSite' => array(
                        'URL' => '',
                        'NotificationURL' => '',
                    ),
                ),

                'hide_in_request' => array(
                    'MerchantSite' => array(
                        'ID' => '',
                        'Created' => '',
                        'Signature' => '',
                        'ApiKey' => '',
                        'Details' => '',
                    ),
                ),

                'request_structure' => $merchantsite_obj,

                'mandatory_in_response' => array(
                    'merchantsite' => array(
                        'id' => 0,
                    ),
                ),

                'response_structure' => $merchantsite_obj,
            ),

            self::FUNC_SITE_EDIT => array(
                'name' => self::s2p_t( 'Edit Merchant Site' ),
                'url_suffix' => '/v1/merchantsites/{*ID*}/',
                'http_method' => 'PATCH',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_request' => array(
                    'MerchantSite' => array(
                        'URL' => '',
                        'NotificationURL' => '',
                    ),
                ),

                'hide_in_request' => array(
                    'MerchantSite' => array(
                        'ID' => '',
                        'Created' => '',
                        'Signature' => '',
                        'ApiKey' => '',
                        'Details' => '',
                    ),
                ),

                'request_structure' => $merchantsite_obj,

                'mandatory_in_response' => array(
                    'merchantsite' => array(
                        'id' => 0,
                    ),
                ),

                'hide_in_response' => array(
                    'merchantsite' => array(
                        'details' => '',
                    ),
                ),

                'response_structure' => $merchantsite_obj,
            ),

            self::FUNC_REGEN_APIKEY => array(
                'name' => self::s2p_t( 'Regenerate Merchant Site API Key' ),
                'url_suffix' => '/v1/merchantsites/{*ID*}/regenerateapikey/',
                'http_method' => 'POST',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'merchantsite' => array(
                        'id' => 0,
                    ),
                ),

                'response_structure' => $merchantsite_obj,
            ),

            self::FUNC_REGEN_SIGNATURE => array(
                'name' => self::s2p_t( 'Regenerate Merchant Site Signature' ),
                'url_suffix' => '/v1/merchantsites/{*ID*}/regeneratesignature/',
                'http_method' => 'POST',

                'get_variables' => array(
                    array(
                        'name' => 'id',
                        'type' => S2P_SDK_Scope_Variable::TYPE_INT,
                        'default' => 0,
                        'mandatory' => true,
                        'move_in_url' => true,
                    ),
                ),

                'mandatory_in_response' => array(
                    'merchantsite' => array(
                        'id' => 0,
                    ),
                ),

                'response_structure' => $merchantsite_obj,
            ),
       );
    }
}