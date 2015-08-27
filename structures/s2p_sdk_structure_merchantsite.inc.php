<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

class S2P_SDK_Structure_Merchantsite extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'merchantsite',
            'external_name' => 'MerchantSite',
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
                'name' => 'id',
                'external_name' => 'ID',
                'display_name' => self::s2p_t( 'Site ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'created',
                'external_name' => 'Created',
                'display_name' => self::s2p_t( 'Site date of creation' ),
                'type' => S2P_SDK_VTYPE_DATETIME,
            ),
            array(
                'name' => 'url',
                'external_name' => 'URL',
                'display_name' => self::s2p_t( 'Site URL' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^(http(s)?:\/\/).{1,512}$',
            ),
            array(
                'name' => 'active',
                'external_name' => 'Active',
                'display_name' => self::s2p_t( 'Site status' ),
                'type' => S2P_SDK_VTYPE_BOOL,
            ),
            array(
                'name' => 'notificationurl',
                'external_name' => 'NotificationURL',
                'display_name' => self::s2p_t( 'Notification URL' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^(http(s)?:\/\/).{1,512}$',
            ),
            array(
                'name' => 'signature',
                'external_name' => 'Signature',
                'display_name' => self::s2p_t( 'Site API signature' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^.+$',
            ),
            array(
                'name' => 'apikey',
                'external_name' => 'ApiKey',
                'display_name' => self::s2p_t( 'Site REST API key' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^.+$',
            ),
            array(
                'name' => 'iplist',
                'external_name' => 'IPList',
                'display_name' => self::s2p_t( 'IPs Whitelist' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3},?)?|^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2},?)?|^((\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})-(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}),?)?$',
            ),
            array(
                'name' => 'details',
                'external_name' => 'Details',
                'type' => S2P_SDK_VTYPE_BLARRAY,
                'default' => null,
                'structure' => array(
                    array(
                        'name' => 'code',
                        'external_name' => 'Code',
                        'type' => S2P_SDK_VTYPE_INT,
                        'default' => 0,
                    ),
                    array(
                        'name' => 'info',
                        'external_name' => 'Info',
                        'type' => S2P_SDK_VTYPE_STRING,
                        'default' => '',
                    ),
                )
            ),
       );
    }

}
