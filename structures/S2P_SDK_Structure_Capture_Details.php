<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Capture_Details extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'capturedetails',
            'external_name' => 'CaptureDetails',
            'type'          => S2P_SDK_VTYPE_BLOB,
            'structure'     => $this->get_structure_definition(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_structure_definition() : array
    {
        $status_obj = new S2P_SDK_Structure_Status();

        return [
            [
                'name'          => 'id',
                'external_name' => 'ID',
                'display_name'  => self::s2p_t('Capture ID'),
                'type'          => S2P_SDK_VTYPE_LONG,
                'default'       => 0,
                'regexp'        => '^\d{1,19}$',
            ],
            [
                'name'          => 'amount',
                'external_name' => 'Amount',
                'display_name'  => self::s2p_t('Capture amount'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'status',
                'external_name' => 'Status',
                'display_name'  => self::s2p_t('Capture status'),
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $status_obj->get_structure_definition(),
            ],
        ];
    }
}
