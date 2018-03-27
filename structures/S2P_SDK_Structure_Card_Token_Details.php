<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Card_Token_Details extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'token',
            'external_name' => 'Token',
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
            //
            // Common and REST specific
            //
            array(
                'name' => 'value',
                'external_name' => 'Value',
                'display_name' => self::s2p_t( 'Token value' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
            ),
            array(
                'name' => 'requiresecuritycode',
                'external_name' => 'RequireSecurityCode',
                'display_name' => self::s2p_t( 'Should payer be asked for CVV/CVC?' ),
                'type' => S2P_SDK_VTYPE_BOOL,
                'default' => null,
                'skip_if_default' => true,
            ),
            array(
                'name' => 'securitycode',
                'external_name' => 'SecurityCode',
                'display_name' => self::s2p_t( 'Token security code' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
            ),
        );
    }

}
