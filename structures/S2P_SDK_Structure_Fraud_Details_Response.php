<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Fraud_Details_Response extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'frauddetailsresponse',
            'external_name' => 'FraudDetailsResponse',
            'type'          => S2P_SDK_VTYPE_BLOB,
            'structure'     => $this->get_structure_definition(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_structure_definition() : array
    {
        return [
            [
                'name'          => 'status',
                'external_name' => 'Status',
                'display_name'  => self::s2p_t('Fraud check status'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'checkmode',
                'external_name' => 'CheckMode',
                'display_name'  => self::s2p_t('Fraud check mode'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'score',
                'external_name' => 'Score',
                'display_name'  => self::s2p_t('Fraud check score'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
            ],
            [
                'name'          => 'reason',
                'external_name' => 'Reason',
                'display_name'  => self::s2p_t('Fraud check reason'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
        ];
    }
}
