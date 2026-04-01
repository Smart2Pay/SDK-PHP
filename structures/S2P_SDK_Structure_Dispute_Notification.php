<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Dispute_Notification extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'dispute',
            'external_name' => 'Dispute',
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
                'display_name'  => self::s2p_t('Dispute ID'),
                'type'          => S2P_SDK_VTYPE_LONG,
                'default'       => 0,
                'regexp'        => '^\d{1,19}$',
            ],
            [
                'name'          => 'siteid',
                'external_name' => 'SiteID',
                'display_name'  => self::s2p_t('Dispute Site ID'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'created',
                'external_name' => 'Created',
                'display_name'  => self::s2p_t('Dispute creation time'),
                'type'          => S2P_SDK_VTYPE_DATETIME,
                'default'       => '',
            ],
            [
                'name'          => 'paymentid',
                'external_name' => 'PaymentID',
                'display_name'  => self::s2p_t('Dispute Payment ID'),
                'type'          => S2P_SDK_VTYPE_LONG,
                'default'       => 0,
                'regexp'        => '^\d{1,19}$',
            ],
            [
                'name'          => 'methodid',
                'external_name' => 'MethodID',
                'display_name'  => self::s2p_t('Payment method used'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'amount',
                'external_name' => 'Amount',
                'display_name'  => self::s2p_t('Payment amount'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'currency',
                'external_name' => 'Currency',
                'display_name'  => self::s2p_t('Payment currency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^[A-Z]{3}$',
                'value_source'  => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ],
            [
                'name'          => 'status',
                'external_name' => 'Status',
                'display_name'  => self::s2p_t('Payment status'),
                'type'          => S2P_SDK_VTYPE_BLOB,
                'structure'     => $status_obj->get_structure_definition(),
            ],
        ];
    }
}
