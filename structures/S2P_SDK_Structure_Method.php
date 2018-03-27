<?php

namespace S2P_SDK;

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
                'display_name' => self::s2p_t( 'Method ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'displayname',
                'external_name' => 'DisplayName',
                'display_name' => self::s2p_t( 'Method display name' ),
                'type' => S2P_SDK_VTYPE_STRING,
            ),
            array(
                'name' => 'description',
                'external_name' => 'Description',
                'display_name' => self::s2p_t( 'Method description' ),
                'type' => S2P_SDK_VTYPE_STRING,
            ),
            array(
                'name' => 'logourl',
                'external_name' => 'LogoURL',
                'display_name' => self::s2p_t( 'Method logo URL' ),
                'type' => S2P_SDK_VTYPE_STRING,
            ),
            array(
                'name' => 'guaranteed',
                'external_name' => 'Guaranteed',
                'display_name' => self::s2p_t( 'Method is guaranteed' ),
                'type' => S2P_SDK_VTYPE_BOOL,
            ),
            array(
                'name' => 'active',
                'external_name' => 'Active',
                'display_name' => self::s2p_t( 'Method status' ),
                'type' => S2P_SDK_VTYPE_BOOL,
            ),
            array(
                'name' => 'supportiframe',
                'external_name' => 'SupportIframe',
                'display_name' => self::s2p_t( 'Method supports IFrame' ),
                'type' => S2P_SDK_VTYPE_BOOL,
            ),
            array(
                'name' => 'iframewidth',
                'external_name' => 'IframeWidth',
                'display_name' => self::s2p_t( 'Method IFrame width' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
            ),
            array(
                'name' => 'iframeheight',
                'external_name' => 'IframeHeight',
                'display_name' => self::s2p_t( 'Method IFrame height' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
            ),
            array(
                'name' => 'countries',
                'external_name' => 'Countries',
                'display_name' => self::s2p_t( 'Method supported countries' ),
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_STRING,
                'default' => array(),
            ),
            array(
                'name' => 'currencies',
                'external_name' => 'Currencies',
                'display_name' => self::s2p_t( 'Method supported currencies' ),
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_STRING,
                'default' => array(),
            ),
            array(
                'name' => 'validatorspayin',
                'external_name' => 'ValidatorsPayin',
                'display_name' => self::s2p_t( 'Method one time payment validations' ),
                'type' => S2P_SDK_VTYPE_BLARRAY,
                'default' => null,
                'structure' => $method_validator_obj->get_structure_definition(),
            ),
            array(
                'name' => 'validatorsrecurrent',
                'external_name' => 'ValidatorsRecurrent',
                'display_name' => self::s2p_t( 'Method recurrent payment validations' ),
                'type' => S2P_SDK_VTYPE_BLARRAY,
                'default' => null,
                'structure' => $method_validator_obj->get_structure_definition(),
            ),
            array(
                'name' => 'options',
                'external_name' => 'Options',
                'display_name' => self::s2p_t( 'Method options' ),
                'type' => S2P_SDK_VTYPE_BLARRAY,
                'default' => null,
                'structure' => $method_option_obj->get_structure_definition(),
            ),
       );
    }

}
