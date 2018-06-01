<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Card_Authentication_Request extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'card_authentication',
            'external_name' => 'CardAuthentication',
            'type' => S2P_SDK_VTYPE_BLOB,
            'structure' => $this->get_structure_definition(),
        );
    }

    /**
     * Function should return structure definition for blobs or array variables
     * @return array
     */
    public function get_structure_definition()
    {
        $customer_obj = new S2P_SDK_Structure_Customer();
        $address_obj = new S2P_SDK_Structure_Address();
        $card_details_obj = new S2P_SDK_Structure_Card_Details();
        $token_details_obj = new S2P_SDK_Structure_Card_Token_Details();

        return array(
            array(
                'name' => 'customer',
                'external_name' => 'Customer',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $customer_obj->get_structure_definition(),
            ),
            array(
                'name' => 'billingaddress',
                'external_name' => 'BillingAddress',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $address_obj->get_structure_definition(),
            ),
            array(
                'name' => 'card',
                'external_name' => 'Card',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $card_details_obj->get_structure_definition(),
            ),
        );
    }

}
