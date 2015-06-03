<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

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
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'country',
                'external_name' => 'Country',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^[a-zA-Z]{2}$',
            ),
            array(
                'name' => 'city',
                'external_name' => 'City',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,40}$',
            ),
            array(
                'name' => 'zipcode',
                'external_name' => 'ZipCode',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,50}$',
            ),
            array(
                'name' => 'state',
                'external_name' => 'State',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,50}$',
            ),
            array(
                'name' => 'street',
                'external_name' => 'Street',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,100}$',
            ),
            array(
                'name' => 'streetnumber',
                'external_name' => 'StreetNumber',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,255}$',
            ),
            array(
                'name' => 'housenumber',
                'external_name' => 'HouseNumber',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,255}$',
            ),
            array(
                'name' => 'houseextension',
                'external_name' => 'HouseExtension',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,255}$',
            ),
        );
    }

}