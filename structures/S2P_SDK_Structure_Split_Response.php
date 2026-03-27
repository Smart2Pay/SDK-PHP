<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Split_Response extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'split',
            'external_name' => 'Split',
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
        $capture_response_obj = new S2P_SDK_Structure_Capture_Response();

        return [
            [
                'name'          => 'id',
                'external_name' => 'ID',
                'display_name'  => self::s2p_t('Payment split ID'),
                'type'          => S2P_SDK_VTYPE_LONG,
                'default'       => 0,
                'regexp'        => '^\d{1,19}$',
            ],
            [
                'name'          => 'paymentid',
                'external_name' => 'PaymentID',
                'display_name'  => self::s2p_t('Payment ID'),
                'type'          => S2P_SDK_VTYPE_LONG,
                'default'       => 0,
                'regexp'        => '^\d{1,19}$',
            ],
            [
                'name'          => 'siteid',
                'external_name' => 'SiteID',
                'display_name'  => self::s2p_t('Refund site ID'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'merchanttransactionid',
                'external_name' => 'MerchantTransactionID',
                'display_name'  => self::s2p_t('Payment merchant assigned transaction ID'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'originatortransactionid',
                'external_name' => 'OriginatorTransactionID',
                'display_name'  => self::s2p_t('A number that uniquely identifies the transaction in the original requester\'s system'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^[0-9a-zA-Z_-]{1,50}$',
            ],
            [
                'name'          => 'created',
                'external_name' => 'Created',
                'display_name'  => self::s2p_t('Split payment creation time'),
                'type'          => S2P_SDK_VTYPE_DATETIME,
                'default'       => '',
            ],
            [
                'name'          => 'amount',
                'external_name' => 'Amount',
                'display_name'  => self::s2p_t('Split payment amount'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'currency',
                'external_name' => 'Currency',
                'display_name'  => self::s2p_t('Split payment currency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^[A-Z]{3}$',
                'value_source'  => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ],
            [
                'name'          => 'capturedamount',
                'external_name' => 'CapturedAmount',
                'display_name'  => self::s2p_t('Payment amount captured'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'                   => 'status',
                'external_name'          => 'Status',
                'type'                   => S2P_SDK_VTYPE_BLOB,
                'structure'              => $status_obj->get_structure_definition(),
                'skip_if_default'        => true,
                'ignore_if_not_in_scope' => true,
            ],
            [
                'name'                   => 'statedetails',
                'external_name'          => 'StateDetails',
                'type'                   => S2P_SDK_VTYPE_BLOB,
                'structure'              => $status_obj->get_structure_definition(),
                'skip_if_default'        => true,
                'ignore_if_not_in_scope' => true,
            ],
            [
                'name'                   => 'capturedetails',
                'external_name'          => 'CaptureDetails',
                'type'                   => S2P_SDK_VTYPE_BLOB,
                'structure'              => $capture_response_obj->get_structure_definition(),
                'skip_if_default'        => true,
                'ignore_if_not_in_scope' => true,
            ],
        ];
    }
}
