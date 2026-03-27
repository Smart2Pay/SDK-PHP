<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Card_Split_Capture_Request extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'payment',
            'external_name' => 'Payment',
            'type'          => S2P_SDK_VTYPE_BLOB,
            'structure'     => $this->get_structure_definition(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_structure_definition() : array
    {
        $split_obj = new S2P_SDK_Structure_Split_Capture_Request();

        return [
            [
                'name'          => 'split',
                'external_name' => 'Split',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $split_obj->get_structure_definition(),
            ],
            [
                'name'          => 'totalcapturecount',
                'external_name' => 'TotalCaptureCount',
                'display_name'  => self::s2p_t('Total number of captures that will be performed on this split'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
        ];
    }
}
