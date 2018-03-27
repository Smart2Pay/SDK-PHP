<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Generic_Error extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'message',
            'external_name' => 'Message',
            'type' => S2P_SDK_VTYPE_STRING,
            'default' => '',
        );
    }

    /**
     * Function should return structure definition for blobs or array variables
     * @return array
     */
    public function get_structure_definition()
    {
        return null;
    }
}
