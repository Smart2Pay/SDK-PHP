<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_status.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_customer.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_address.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_article.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_payment_customer_details.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_payment_reference_details.inc.php' );

class S2P_SDK_Structure_Payment_Response extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'payment',
            'external_name' => 'Payment',
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
        $status_obj = new S2P_SDK_Structure_Status();
        $customer_obj = new S2P_SDK_Structure_Customer();
        $address_obj = new S2P_SDK_Structure_Address();
        $article_obj = new S2P_SDK_Structure_Article();
        $customer_details_obj = new S2P_SDK_Structure_Payment_Customer_Details();
        $reference_details_obj = new S2P_SDK_Structure_Payment_Reference_Details();

        return array(
            array(
                'name' => 'id',
                'external_name' => 'ID',
                'display_name' => self::s2p_t( 'Payment ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'created',
                'external_name' => 'Created',
                'display_name' => self::s2p_t( 'Payment creation time' ),
                'type' => S2P_SDK_VTYPE_DATETIME,
                'default' => '',
            ),
            array(
                'name' => 'merchanttransactionid',
                'external_name' => 'MerchantTransactionID',
                'display_name' => self::s2p_t( 'Payment merchant assigned transaction ID' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^[0-9a-zA-Z_-]{1,50}$',
            ),
            array(
                'name' => 'amount',
                'external_name' => 'Amount',
                'display_name' => self::s2p_t( 'Payment amount' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'currency',
                'external_name' => 'Currency',
                'display_name' => self::s2p_t( 'Payment currency' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^[A-Z]{3}$',
                'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ),
            array(
                'name' => 'returnurl',
                'external_name' => 'ReturnURL',
                'display_name' => self::s2p_t( 'Payment return URL' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
            ),
            array(
                'name' => 'description',
                'external_name' => 'Description',
                'display_name' => self::s2p_t( 'Payment description' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,255}$',
            ),
            array(
                'name' => 'methodid',
                'external_name' => 'MethodID',
                'display_name' => self::s2p_t( 'Payment currency' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^([0-9]{1,10})$',
            ),
            array(
                'name' => 'methodoptionid',
                'external_name' => 'MethodOptionID',
                'display_name' => self::s2p_t( 'Payment method ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^([0-9]{1,10})$',
            ),
            array(
                'name' => 'siteid',
                'external_name' => 'SiteID',
                'display_name' => self::s2p_t( 'Payment site ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^([0-9]{1,10})$',
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
                'name' => 'shippingaddress',
                'external_name' => 'ShippingAddress',
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
                'name' => 'referencedetails',
                'external_name' => 'ReferenceDetails',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $reference_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'details',
                'external_name' => 'Details',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $customer_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'customparameters',
                'external_name' => 'CustomParameters',
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_STRING,
                'default' => null,
            ),
            array(
                'name' => 'preapprovalid',
                'external_name' => 'PreapprovalID',
                'display_name' => self::s2p_t( 'Payment preapproval ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'status',
                'external_name' => 'Status',
                'display_name' => self::s2p_t( 'Payment status' ),
                'type' => S2P_SDK_VTYPE_BLOB,
                'structure' => $status_obj->get_structure_definition(),
            ),
            array(
                'name' => 'methodtransactionid',
                'external_name' => 'MethodTransactionID',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'tokenlifetime',
                'external_name' => 'TokenLifetime',
                'display_name' => self::s2p_t( 'Payment token lifetime' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'redirecturl',
                'external_name' => 'RedirectURL',
                'display_name' => self::s2p_t( 'Payment redirect URL' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
            ),
        );
    }

}
