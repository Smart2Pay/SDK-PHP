<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

class S2P_SDK_Structure_Method_Validator extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'validator',
            'external_name' => 'Validator',
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
                'name' => 'source',
                'external_name' => 'Source',
                'type' => S2P_SDK_VTYPE_STRING,
            ),
            array(
                'name' => 'regex',
                'external_name' => 'Regex',
                'type' => S2P_SDK_VTYPE_STRING,
            ),
            array(
                'name' => 'required',
                'external_name' => 'Required',
                'type' => S2P_SDK_VTYPE_BOOL,
            ),
      );
    }

}