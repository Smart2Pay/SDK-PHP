<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Payout_Response_List extends S2P_SDK_Structure_Payout_Response
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'payouts',
            'external_name' => 'Payouts',
            'type'          => S2P_SDK_VTYPE_BLARRAY,
            'structure'     => $this->get_structure_definition(),
            'default'       => null,
        ];
    }
}
