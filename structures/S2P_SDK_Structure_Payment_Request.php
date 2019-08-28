<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Payment_Request extends S2P_SDK_Scope_Structure
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
        $customer_obj = new S2P_SDK_Structure_Customer();
        $address_obj = new S2P_SDK_Structure_Address();
        $article_obj = new S2P_SDK_Structure_Article();
        $payment_details_obj = new S2P_SDK_Structure_Payment_Details();
        $preapproval_details_obj = new S2P_SDK_Structure_Preapproval_details();

        return array(
            array(
                'name' => 'skinid',
                'external_name' => 'SkinID',
                'display_name' => self::s2p_t( 'Skin ID to be used' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => null,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'clientip',
                'external_name' => 'ClientIP',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => null,
                'skip_if_default' => true,
                'regexp' => S2P_SDK_Module::IP_REGEXP,
            ),
            array(
                'name' => 'merchanttransactionid',
                'external_name' => 'MerchantTransactionID',
                'display_name' => self::s2p_t( 'Payment merchant assigned transaction ID' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => null,
                'regexp' => '^[0-9a-zA-Z_-]{1,50}$',
            ),
            array(
                'name' => 'originatortransactionid',
                'external_name' => 'OriginatorTransactionID',
                'display_name' => self::s2p_t( 'A number that uniquely identifies the transaction in the original requester\'s system' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => null,
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
                'default' => null,
                'regexp' => '^[a-zA-Z]{3}$',
                'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ),
            array(
                'name' => 'returnurl',
                'external_name' => 'ReturnURL',
                'display_name' => self::s2p_t( 'Payment return URL' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => null,
                'regexp' => '^(http(s)?(:\/\/|%3A%2F%2F).{1,512})?$',
                'check_constant' => 'S2P_SDK_PAYMENT_RETURN_URL',
            ),
            array(
                'name' => 'description',
                'external_name' => 'Description',
                'display_name' => self::s2p_t( 'Payment description' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^.{1,255}$',
            ),
            array(
                'name' => 'preapprovalid',
                'external_name' => 'PreapprovalID',
                'display_name' => self::s2p_t( 'Payment preapproval ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'methodid',
                'external_name' => 'MethodID',
                'display_name' => self::s2p_t( 'Payment method ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => null,
                'regexp' => '^([0-9]{1,10})$',
                'value_source' => S2P_SDK_Values_Source::TYPE_AVAILABLE_METHODS,
            ),
            array(
                'name' => 'methodoptionid',
                'external_name' => 'MethodOptionID',
                'display_name' => self::s2p_t( 'Payment method option ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^([0-9]{1,10})$',
            ),
            array(
                'name' => 'guaranteed',
                'external_name' => 'Guaranteed',
                'type' => S2P_SDK_VTYPE_BOOL,
                'display_name' => self::s2p_t( 'Try using guaranteed payment methods' ),
            ),
            array(
                'name' => 'redirectiniframe',
                'external_name' => 'RedirectInIframe',
                'display_name' => self::s2p_t( 'Payment redirect in IFrame' ),
                'type' => S2P_SDK_VTYPE_BOOL,
            ),
            array(
                'name' => 'redirectmerchantiniframe',
                'external_name' => 'RedirectMerchantInIframe',
                'display_name' => self::s2p_t( 'Payment redirect in IFrame' ),
                'type' => S2P_SDK_VTYPE_BOOL,
            ),
            array(
                'name' => 'includemethodids',
                'external_name' => 'IncludeMethodIDs',
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_INT,
                'default' => null,
            ),
            array(
                'name' => 'excludemethodids',
                'external_name' => 'ExcludeMethodIDs',
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_INT,
                'default' => null,
            ),
            array(
                'name' => 'prioritizemethodids',
                'external_name' => 'PrioritizeMethodIDs',
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_INT,
                'default' => null,
            ),
            array(
                'name' => 'details',
                'external_name' => 'Details',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $payment_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'preapprovaldetails',
                'external_name' => 'PreapprovalDetails',
                'display_name' => self::s2p_t( 'Preapproval details' ),
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $preapproval_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'customparameters',
                'external_name' => 'CustomParameters',
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_STRING,
                'array_numeric_keys' => false,
                'default' => null,
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
                'name' => 'language',
                'external_name' => 'Language',
                'display_name' => self::s2p_t( 'Language used' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
            ),
            array(
                'name' => 'tokenlifetime',
                'external_name' => 'TokenLifetime',
                'display_name' => self::s2p_t( 'Payment token lifetime' ),
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
            ),
        );
    }

}
