<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) or !defined( 'S2P_SDK_DIR_CLASSES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_customer.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_address.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_article.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_card_details.inc.php' );
include_once(S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_card_token_details.inc.php');
include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_values_source.inc.php' );

class S2P_SDK_Structure_Card_Payment_Request extends S2P_SDK_Scope_Structure
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
        $card_details_obj = new S2P_SDK_Structure_Card_Details();
        $token_details_obj = new S2P_SDK_Structure_Card_Token_Details();

        return array(
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
                'default' => null,
                'regexp' => '^[a-zA-Z]{3}$',
                'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ),
            array(
                'name' => 'returnurl',
                'external_name' => 'ReturnURL',
                'display_name' => self::s2p_t( 'Payment return URL' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
                'check_constant' => 'S2P_SDK_PAYMENT_RETURN_URL',
            ),
            array(
                'name' => 'description',
                'external_name' => 'Description',
                'display_name' => self::s2p_t( 'Payment description' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^(.{1,500})?$',
            ),
            array(
                'name' => 'statementdescriptor',
                'external_name' => 'StatementDescriptor',
                'display_name' => self::s2p_t( 'Payment statement description' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^(.{1,250})?$',
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
                'name' => 'customer',
                'external_name' => 'Customer',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $customer_obj->get_structure_definition(),
            ),
            array(
                'name' => 'card',
                'external_name' => 'Card',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $card_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'token',
                'external_name' => 'Token',
                'display_name' => self::s2p_t( 'Token structure' ),
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $token_details_obj->get_structure_definition(),
            ),
            array(
                'name' => 'capture',
                'external_name' => 'Capture',
                'display_name' => self::s2p_t( 'Should capture payment?' ),
                'type' => S2P_SDK_VTYPE_BOOL,
                'default' => null,
                'skip_if_default' => true,
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
                'name' => 'reusetoken',
                'external_name' => 'ReuseToken',
                'display_name' => self::s2p_t( 'Should re-use token?' ),
                'type' => S2P_SDK_VTYPE_BOOL,
                'default' => null,
                'skip_if_default' => true,
            ),
        );
    }

}