<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Merchantsite_List extends S2P_SDK_Structure_Merchantsite
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'merchantsites',
            'external_name' => 'MerchantSites',
            'type'          => S2P_SDK_VTYPE_BLARRAY,
            'structure'     => $this->get_structure_definition(),
            'default'       => null,
        ];
    }
}
