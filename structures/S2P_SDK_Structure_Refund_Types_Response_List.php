<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Refund_Types_Response_List extends S2P_SDK_Structure_Refund_Types_Response
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'refundtypes',
            'external_name' => 'RefundTypes',
            'type'          => S2P_SDK_VTYPE_BLARRAY,
            'structure'     => $this->get_structure_definition(),
        ];
    }
}
