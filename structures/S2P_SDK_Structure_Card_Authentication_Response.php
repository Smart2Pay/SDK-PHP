<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Card_Authentication_Response extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'card_authentication',
            'external_name' => 'CardAuthentication',
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
        $card_details_obj = new S2P_SDK_Structure_Card_Details();
        $token_details_obj = new S2P_SDK_Structure_Card_Token_Details();
        $status_obj = new S2P_SDK_Structure_Status();

        return [
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
                'name'          => 'card',
                'external_name' => 'Card',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $card_details_obj->get_structure_definition(),
            ],
            [
                'name'          => 'creditcardtoken',
                'external_name' => 'CreditCardToken',
                'display_name'  => self::s2p_t('Credit card token structure'),
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $token_details_obj->get_structure_definition(),
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
