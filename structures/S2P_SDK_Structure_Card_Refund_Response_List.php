<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Card_Refund_Response_List extends S2P_SDK_Structure_Card_Refund_Response
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'refunds',
            'external_name' => 'Refunds',
            'type' => S2P_SDK_VTYPE_BLARRAY,
            'structure' => $this->get_structure_definition(),
        );
    }

}
