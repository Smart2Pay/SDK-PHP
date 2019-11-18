<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Capture_Response_List extends S2P_SDK_Structure_Capture_Response
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'captures',
            'external_name' => 'Captures',
            'type' => S2P_SDK_VTYPE_BLARRAY,
            'structure' => $this->get_structure_definition(),
        );
    }

}
