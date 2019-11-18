<?php

namespace S2P_SDK;

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
        $payment_details_obj = new S2P_SDK_Structure_Payment_Details();
        $reference_details_obj = new S2P_SDK_Structure_Payment_Reference_Details();
        $token_details_obj = new S2P_SDK_Structure_Card_Token_Details();
        $card_details_obj = new S2P_SDK_Structure_Card_Details();
        $preapproval_details_obj = new S2P_SDK_Structure_Preapproval_details();
        $fraud_details_obj = new S2P_SDK_Structure_Fraud_Details_Response();
        $td_secure_obj = new S2P_SDK_Structure_3D_Secure_Data();
        $device_info_obj = new S2P_SDK_Structure_Device_Info();
        $card_on_file_obj = new S2P_SDK_Structure_Card_On_File();
        $capture_details_obj = new S2P_SDK_Structure_Capture_Details();

        return array(
            //
            // Common and REST specific
            //
            array(
                'name' => 'id',
                'external_name' => 'ID',
                'display_name' => self::s2p_t( 'Payment ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'skinid',
                'external_name' => 'SkinID',
                'display_name' => self::s2p_t( 'Skin ID to be used' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'clientip',
                'external_name' => 'ClientIP',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => null,
                'regexp' => S2P_SDK_Module::IP_REGEXP,
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
                'name' => 'originatortransactionid',
                'external_name' => 'OriginatorTransactionID',
                'display_name' => self::s2p_t( 'A number that uniquely identifies the transaction in the original requester\'s system' ),
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
                'name' => 'capturedamount',
                'external_name' => 'CapturedAmount',
                'display_name' => self::s2p_t( 'Payment amount captured' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
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
                'display_name' => self::s2p_t( 'Payment method used' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
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
                'name' => 'siteid',
                'external_name' => 'SiteID',
                'display_name' => self::s2p_t( 'Payment site ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^([0-9]{1,10})$',
            ),
            array(
                'name' => 'notificationdatetime',
                'external_name' => 'NotificationDateTime',
                'display_name' => self::s2p_t( 'Date and time of payment notification' ),
                'type' => S2P_SDK_VTYPE_DATETIME,
                'default' => '',
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
                'name' => 'details',
                'external_name' => 'Details',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $payment_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'capturedetails',
                'external_name' => 'CaptureDetails',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $capture_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'referencedetails',
                'external_name' => 'ReferenceDetails',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $reference_details_obj->get_structure_definition(),
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
                'display_name' => self::s2p_t( 'The transaction ID from the payment method provider, can be used for customer support.' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'authorizationcode',
                'external_name' => 'AuthorizationCode',
                'display_name' => self::s2p_t( 'Acquirer Authorization Code' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => null,
                'skip_if_default' => true,
            ),
            array(
                'name' => 'capture',
                'external_name' => 'Capture',
                'display_name' => self::s2p_t( 'Tells if payment was captured' ),
                'type' => S2P_SDK_VTYPE_BOOL,
                'default' => null,
            ),
            array(
                'name' => 'tokenlifetime',
                'external_name' => 'TokenLifetime',
                'display_name' => self::s2p_t( 'Payment token lifetime' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
                'skip_if_default' => true,
            ),
            array(
                'name' => 'paymenttokenlifetime',
                'external_name' => 'PaymentTokenLifetime',
                'display_name' => self::s2p_t( 'Payment token lifetime' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
                'skip_if_default' => true,
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
                'name' => 'redirecturl',
                'external_name' => 'RedirectURL',
                'display_name' => self::s2p_t( 'Payment redirect URL' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
            ),
            //
            // END Common and REST specific
            //

            array(
                'name' => 'preapprovaldetails',
                'external_name' => 'PreapprovalDetails',
                'display_name' => self::s2p_t( 'Preapproval details' ),
                'type' => S2P_SDK_VTYPE_BLOB,
                'structure' => $preapproval_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'invalidrequestid',
                'external_name' => 'InvalidRequestID',
                'display_name' => self::s2p_t( 'Card failure reference ID' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'statementdescriptor',
                'external_name' => 'StatementDescriptor',
                'display_name' => self::s2p_t( 'Statement descriptor' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'creditcardtoken',
                'external_name' => 'CreditCardToken',
                'display_name' => self::s2p_t( 'Credit card token structure' ),
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $token_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'installments',
                'external_name' => 'Installments',
                'display_name' => self::s2p_t( 'Payment split into installments' ),
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'retry',
                'external_name' => 'Retry',
                'display_name' => self::s2p_t( 'Should retry payment?' ),
                'type' => S2P_SDK_VTYPE_BOOL,
                'default' => null,
                'skip_if_default' => true,
            ),
            array(
                'name' => '3dsecure',
                'external_name' => '3DSecure',
                'display_name' => self::s2p_t( 'Should try a 3D secure payment?' ),
                'type' => S2P_SDK_VTYPE_BOOL,
                'default' => null,
                'skip_if_default' => true,
            ),
            array(
                'name' => '3dsecuredata',
                'external_name' => '3DSecureData',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'skip_if_default' => true,
                'structure' => $td_secure_obj->get_structure_definition(),
            ),
            array(
                'name' => 'deviceinfo',
                'external_name' => 'DeviceInfo',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'skip_if_default' => true,
                'structure' => $device_info_obj->get_structure_definition(),
            ),
            array(
                'name' => 'scaexemption',
                'external_name' => 'ScaExemption',
                'display_name' => self::s2p_t( 'Sca exemption' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'skip_if_default' => true,
            ),
            array(
                'name' => 'cardonfile',
                'external_name' => 'CardOnFile',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'skip_if_default' => true,
                'structure' => $card_on_file_obj->get_structure_definition(),
            ),
            array(
                'name' => 'card',
                'external_name' => 'Card',
                'display_name' => self::s2p_t( 'Card structure' ),
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $card_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'fraud',
                'external_name' => 'Fraud',
                'display_name' => self::s2p_t( 'Fraud check structure' ),
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $fraud_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'moto',
                'external_name' => 'Moto',
                'display_name' => self::s2p_t( 'If set to true, the payment will be marked at the acquirer as Mail Order Telephone Order type of transaction. This is not available for all acquirers.' ),
                'type' => S2P_SDK_VTYPE_BOOL,
                'default' => null,
                'skip_if_default' => true,
            ),
        );
    }

}
