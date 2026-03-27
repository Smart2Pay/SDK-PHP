<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Preapproval_Response_List extends S2P_SDK_Structure_Preapproval_Response
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'preapprovals',
            'external_name' => 'Preapprovals',
            'type'          => S2P_SDK_VTYPE_BLARRAY,
            'structure'     => $this->get_structure_definition(),
        ];
    }
}
