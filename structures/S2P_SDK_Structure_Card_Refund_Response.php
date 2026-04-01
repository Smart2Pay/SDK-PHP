<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Card_Refund_Response extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'refund',
            'external_name' => 'Refund',
            'type'          => S2P_SDK_VTYPE_BLOB,
            'structure'     => $this->get_structure_definition(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_structure_definition() : array
    {
        $customer_obj = new S2P_SDK_Structure_Customer();
        $address_obj = new S2P_SDK_Structure_Address();
        $article_obj = new S2P_SDK_Structure_Article();
        $status_obj = new S2P_SDK_Structure_Status();

        return [
            [
                'name'          => 'invalidrequestid',
                'external_name' => 'InvalidRequestID',
                'display_name'  => self::s2p_t('Refund failure reference ID'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'id',
                'external_name' => 'ID',
                'display_name'  => self::s2p_t('Refund ID'),
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
                'name'          => 'created',
                'external_name' => 'Created',
                'display_name'  => self::s2p_t('Refund creation date and time'),
                'type'          => S2P_SDK_VTYPE_DATETIME,
                'default'       => '',
            ],
            [
                'name'          => 'merchanttransactionid',
                'external_name' => 'MerchantTransactionID',
                'display_name'  => self::s2p_t('Refund merchant assigned transaction ID'),
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
                'name'          => 'initialpaymentid',
                'external_name' => 'InitialPaymentID',
                'display_name'  => self::s2p_t('Refund initial payment ID'),
                'type'          => S2P_SDK_VTYPE_LONG,
                'default'       => 0,
                'regexp'        => '^\d{1,19}$',
            ],
            [
                'name'          => 'methodtransactionid',
                'external_name' => 'MethodTransactionID',
                'display_name'  => self::s2p_t('The transaction ID from the payment method provider, can be used for customer support.'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'amount',
                'external_name' => 'Amount',
                'display_name'  => self::s2p_t('Refund amount'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'currency',
                'external_name' => 'Currency',
                'display_name'  => self::s2p_t('Refund currency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^[A-Z]{3}$',
                'value_source'  => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ],
            [
                'name'          => 'description',
                'external_name' => 'Description',
                'display_name'  => self::s2p_t('Refund description'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,255}$',
            ],
            [
                'name'          => 'statementdescriptor',
                'external_name' => 'StatementDescriptor',
                'display_name'  => self::s2p_t('Refund statement description'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,255}$',
            ],
            [
                'name'          => 'captureid',
                'external_name' => 'CaptureID',
                'display_name'  => self::s2p_t('Refunded capture ID'),
                'type'          => S2P_SDK_VTYPE_LONG,
                'default'       => 0,
                'regexp'        => '^\d{1,19}$',
            ],
            [
                'name'          => 'customer',
                'external_name' => 'Customer',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $customer_obj->get_structure_definition(),
            ],
            [
                'name'          => 'billingaddress',
                'external_name' => 'BillingAddress',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $address_obj->get_structure_definition(),
            ],
            [
                'name'          => 'bankaddress',
                'external_name' => 'BankAddress',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $address_obj->get_structure_definition(),
            ],
            [
                'name'          => 'articles',
                'external_name' => 'Articles',
                'type'          => S2P_SDK_VTYPE_BLARRAY,
                'default'       => null,
                'structure'     => $article_obj->get_structure_definition(),
            ],
            [
                'name'          => 'status',
                'external_name' => 'Status',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'structure'     => $status_obj->get_structure_definition(),
            ],
        ];
    }
}
