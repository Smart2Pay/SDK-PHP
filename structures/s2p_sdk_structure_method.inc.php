<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_method_validator.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_method_option.inc.php' );

class S2P_SDK_Structure_Method extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'method',
            'external_name' => 'Method',
            'type' => S2P_SDK_VTYPE_BLOB,
            'structure' => $this->get_structure_definition(),
        );
    }

    /**
     * Function should return structure definition for blobs or array variables
     * @return array
     */
    public function get_structure_definition()
    {
        $method_validator_obj = new S2P_SDK_Structure_Method_Validator();
        $method_option_obj = new S2P_SDK_Structure_Method_Option();

        return array(
            array(
                'name' => 'id',
                'external_name' => 'ID',
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'displayname',
                'external_name' => 'DisplayName',
                'type' => S2P_SDK_VTYPE_STRING,
            ),
            array(
                'name' => 'description',
                'external_name' => 'Description',
                'type' => S2P_SDK_VTYPE_STRING,
            ),
            array(
                'name' => 'logourl',
                'external_name' => 'LogoURL',
                'type' => S2P_SDK_VTYPE_STRING,
            ),
            array(
                'name' => 'guaranteed',
                'external_name' => 'Guaranteed',
                'type' => S2P_SDK_VTYPE_BOOL,
            ),
            array(
                'name' => 'active',
                'external_name' => 'Active',
                'type' => S2P_SDK_VTYPE_BOOL,
            ),
            array(
                'name' => 'supportiframe',
                'external_name' => 'SupportIframe',
                'type' => S2P_SDK_VTYPE_BOOL,
            ),
            array(
                'name' => 'iframewidth',
                'external_name' => 'IframeWidth',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
            ),
            array(
                'name' => 'iframeheight',
                'external_name' => 'IframeHeight',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
            ),
            array(
                'name' => 'countries',
                'external_name' => 'Countries',
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_STRING,
                'default' => array(),
            ),
            array(
                'name' => 'currencies',
                'external_name' => 'Currencies',
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_STRING,
                'default' => array(),
            ),
            array(
                'name' => 'validatorspayin',
                'external_name' => 'ValidatorsPayin',
                'type' => S2P_SDK_VTYPE_BLARRAY,
                'default' => null,
                'structure' => $method_validator_obj->get_structure_definition(),
            ),
            array(
                'name' => 'validatorsrecurrent',
                'external_name' => 'ValidatorsRecurrent',
                'type' => S2P_SDK_VTYPE_BLARRAY,
                'default' => null,
                'structure' => $method_validator_obj->get_structure_definition(),
            ),
            array(
                'name' => 'options',
                'external_name' => 'Options',
                'type' => S2P_SDK_VTYPE_BLARRAY,
                'default' => null,
                'structure' => $method_option_obj->get_structure_definition(),
            ),
       );
    }

}