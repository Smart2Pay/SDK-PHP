<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Exchangerate_Response extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'exchangerate',
            'external_name' => 'ExchangeRate',
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
                'name' => 'from',
                'external_name' => 'From',
                'display_name' => self::s2p_t( 'From currency' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => null,
                'regexp' => '^[a-zA-Z]{3}$',
                'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ),
            array(
                'name' => 'to',
                'external_name' => 'To',
                'display_name' => self::s2p_t( 'To currency' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => null,
                'regexp' => '^[a-zA-Z]{3}$',
                'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ),
            array(
                'name' => 'datetime',
                'external_name' => 'DateTime',
                'display_name' => self::s2p_t( 'Last update date and time' ),
                'type' => S2P_SDK_VTYPE_DATETIME,
            ),
            array(
                'name' => 'rate',
                'external_name' => 'Rate',
                'display_name' => self::s2p_t( 'Conversion rate' ),
                'type' => S2P_SDK_VTYPE_FLOAT,
            ),
       );
    }

}
