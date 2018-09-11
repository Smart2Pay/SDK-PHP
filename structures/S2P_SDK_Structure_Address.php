<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Address extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'address',
            'external_name' => 'Address',
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
                'display_name' => self::s2p_t( 'Address ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
                'skip_if_default' => true,
            ),
            array(
                'name' => 'country',
                'external_name' => 'Country',
                'display_name' => self::s2p_t( 'Address country' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^([A-Za-z]{2})?$',
                'value_source' => S2P_SDK_Values_Source::TYPE_COUNTRY,
            ),
            array(
                'name' => 'city',
                'external_name' => 'City',
                'display_name' => self::s2p_t( 'Address city' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^(.{1,255})?$',
            ),
            array(
                'name' => 'zipcode',
                'external_name' => 'ZipCode',
                'display_name' => self::s2p_t( 'Address zipcode' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^(.{1,255})?$',
            ),
            array(
                'name' => 'state',
                'external_name' => 'State',
                'display_name' => self::s2p_t( 'Address state' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^(.{1,255})?$',
            ),
            array(
                'name' => 'street',
                'external_name' => 'Street',
                'display_name' => self::s2p_t( 'Address street name' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^(.{1,255})?$',
            ),
            array(
                'name' => 'streetnumber',
                'external_name' => 'StreetNumber',
                'display_name' => self::s2p_t( 'Address street number' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^(.{1,255})?$',
            ),
            array(
                'name' => 'housenumber',
                'display_name' => self::s2p_t( 'Address house number' ),
                'external_name' => 'HouseNumber',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^(.{1,255})?$',
            ),
            array(
                'name' => 'houseextension',
                'external_name' => 'HouseExtension',
                'display_name' => self::s2p_t( 'Address house extension' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
                'regexp' => '^(.{1,255})?$',
            ),
        );
    }

}
