<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Split_Request extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'split',
            'external_name' => 'Split',
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
        return array(
            array(
                'name' => 'siteid',
                'external_name' => 'SiteID',
                'display_name' => self::s2p_t( 'Refund site ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'amount',
                'external_name' => 'Amount',
                'display_name' => self::s2p_t( 'Split payment amount' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'merchanttransactionid',
                'external_name' => 'MerchantTransactionID',
                'display_name' => self::s2p_t( 'Payment merchant assigned transaction ID' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'originatortransactionid',
                'external_name' => 'OriginatorTransactionID',
                'display_name' => self::s2p_t( 'A number that uniquely identifies the transaction in the original requester\'s system' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^[0-9a-zA-Z_-]{1,50}$',
                'skip_if_default' => true,
            ),
        );
    }

}
