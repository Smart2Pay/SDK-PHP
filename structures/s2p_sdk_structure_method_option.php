<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Method_Option extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'methodoption',
            'external_name' => 'MethodOption',
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
       );
    }

}
