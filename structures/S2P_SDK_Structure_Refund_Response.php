<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Refund_Response extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'refund',
            'external_name' => 'Refund',
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
        $refund_details_obj = new S2P_SDK_Structure_Refund_Details();
        $address_obj = new S2P_SDK_Structure_Address();
        $article_obj = new S2P_SDK_Structure_Article();
        $status_obj = new S2P_SDK_Structure_Status();

        return array(
            array(
                'name' => 'id',
                'external_name' => 'ID',
                'display_name' => self::s2p_t( 'Refund ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'created',
                'external_name' => 'Created',
                'display_name' => self::s2p_t( 'Refund creation date and time' ),
                'type' => S2P_SDK_VTYPE_DATETIME,
                'default' => '',
            ),
            array(
                'name' => 'merchanttransactionid',
                'external_name' => 'MerchantTransactionID',
                'display_name' => self::s2p_t( 'Refund merchant assigned transaction ID' ),
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
            ),
            array(
                'name' => 'initialpaymentid',
                'external_name' => 'InitialPaymentID',
                'display_name' => self::s2p_t( 'Refund initial payment ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'methodtransactionid',
                'external_name' => 'MethodTransactionID',
                'display_name' => self::s2p_t( 'The transaction ID from the payment method provider, can be used for customer support.' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'amount',
                'external_name' => 'Amount',
                'display_name' => self::s2p_t( 'Refund amount' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'currency',
                'external_name' => 'Currency',
                'display_name' => self::s2p_t( 'Refund currency' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^[A-Z]{3}$',
                'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ),
            array(
                'name' => 'description',
                'external_name' => 'Description',
                'display_name' => self::s2p_t( 'Refund description' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^.{1,255}$',
            ),
            array(
                'name' => 'typeid',
                'external_name' => 'TypeID',
                'display_name' => self::s2p_t( 'Refund type ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'siteid',
                'external_name' => 'SiteID',
                'display_name' => self::s2p_t( 'Refund site ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'details',
                'external_name' => 'Details',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $refund_details_obj->get_structure_definition(),
            ),
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
                'name' => 'bankaddress',
                'external_name' => 'BankAddress',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $address_obj->get_structure_definition(),
            ),
            array(
                'name' => 'articles',
                'external_name' => 'Articles',
                'type' => S2P_SDK_VTYPE_BLARRAY,
                'default' => null,
                'structure' => $article_obj->get_structure_definition(),
            ),
            array(
                'name' => 'status',
                'external_name' => 'Status',
                'type' => S2P_SDK_VTYPE_BLOB,
                'structure' => $status_obj->get_structure_definition(),
            ),
        );
    }

}
