<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Card_Details extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'card',
            'external_name' => 'Card',
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
        return array(
            array(
                'name' => 'holdername',
                'external_name' => 'HolderName',
                'display_name' => self::s2p_t( 'Card holder name' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^([A-Z a-z]{1,50})$',
            ),
            array(
                'name' => 'number',
                'external_name' => 'Number',
                'display_name' => self::s2p_t( 'Card number' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
            ),
            array(
                'name' => 'expirationmonth',
                'external_name' => 'ExpirationMonth',
                'display_name' => self::s2p_t( 'Card expiration month' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^(0[1-9]|1[0-2])$',
            ),
            array(
                'name' => 'expirationyear',
                'external_name' => 'ExpirationYear',
                'display_name' => self::s2p_t( 'Card expiration year' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^(19|20)([0-9]{2})$',
            ),
            array(
                'name' => 'securitycode',
                'external_name' => 'SecurityCode',
                'display_name' => self::s2p_t( 'Card security code (CVV2)' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^([0-9]){3,4}$',
            ),
            array(
                'name' => 'token',
                'external_name' => 'Token',
                'display_name' => self::s2p_t( 'Card token' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
            ),
            array(
                'name' => 'maskednumber',
                'external_name' => 'MaskedNumber',
                'display_name' => self::s2p_t( 'Card masked number' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
            ),
        );
    }

}
