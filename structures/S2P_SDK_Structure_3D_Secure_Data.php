<?php

namespace S2P_SDK;

class S2P_SDK_Structure_3D_Secure_Data extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => '3dsecuredata',
            'external_name' => '3DSecureData',
            'type' => S2P_SDK_VTYPE_BLOB,
            'structure' => $this->get_structure_definition(),
        );
    }

    /**
     * @inheritDoc
     */
    public function get_structure_definition()
    {
        return array(
            array(
                'name' => 'authenticationstatus',
                'external_name' => 'AuthenticationStatus',
                'display_name' => self::s2p_t( 'Authentication status' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'eci',
                'external_name' => 'ECI',
                'display_name' => self::s2p_t( 'ECI' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'cavv',
                'external_name' => 'CAVV',
                'display_name' => self::s2p_t( 'CAVV' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'dsid',
                'external_name' => 'DSID',
                'display_name' => self::s2p_t( 'DSID' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => '3dsecureversion',
                'external_name' => '3DSecureVersion',
                'display_name' => self::s2p_t( '3D secure version' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
        );
    }

}
