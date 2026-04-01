<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Card_Refund_Response_List extends S2P_SDK_Structure_Card_Refund_Response
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'refunds',
            'external_name' => 'Refunds',
            'type'          => S2P_SDK_VTYPE_BLARRAY,
            'structure'     => $this->get_structure_definition(),
        ];
    }
}
