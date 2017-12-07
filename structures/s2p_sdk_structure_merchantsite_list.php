<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Merchantsite_List extends S2P_SDK_Structure_Merchantsite
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'merchantsites',
            'external_name' => 'MerchantSites',
            'type' => S2P_SDK_VTYPE_BLARRAY,
            'structure' => $this->get_structure_definition(),
            'default' => null,
        );
    }

}
