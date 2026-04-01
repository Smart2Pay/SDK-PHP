<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Refund_Types_Response extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'refundtype',
            'external_name' => 'RefundType',
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

        return [
            [
                'name'          => 'name',
                'external_name' => 'Name',
                'display_name'  => self::s2p_t('Refund type name'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'id',
                'external_name' => 'ID',
                'display_name'  => self::s2p_t('Refund type ID'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'allowspartialrefund',
                'external_name' => 'AllowsPartialRefund',
                'display_name'  => self::s2p_t('Refund allows partial refund'),
                'type'          => S2P_SDK_VTYPE_BOOL,
                'default'       => false,
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
                'name'          => 'details',
                'external_name' => 'Details',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $refund_details_obj->get_structure_definition(),
            ],
        ];
    }
}
