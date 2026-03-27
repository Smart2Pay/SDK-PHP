<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Refund_Request extends S2P_SDK_Scope_Structure
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
        $refund_details_obj = new S2P_SDK_Structure_Refund_Details();
        $address_obj = new S2P_SDK_Structure_Address();
        $article_obj = new S2P_SDK_Structure_Article();

        return [
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
                'name'          => 'amount',
                'external_name' => 'Amount',
                'display_name'  => self::s2p_t('Refund amount'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'description',
                'external_name' => 'Description',
                'display_name'  => self::s2p_t('Refund description'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,255}$',
            ],
            [
                'name'          => 'details',
                'external_name' => 'Details',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $refund_details_obj->get_structure_definition(),
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
                'name'          => 'tokenlifetime',
                'external_name' => 'TokenLifetime',
                'display_name'  => self::s2p_t('Refund token lifetime'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
        ];
    }
}
