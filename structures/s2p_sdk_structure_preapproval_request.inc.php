<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) or !defined( 'S2P_SDK_DIR_CLASSES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_customer.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_address.inc.php' );
include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_values_source.inc.php' );

class S2P_SDK_Structure_Preapproval_Request extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'preapproval',
            'external_name' => 'Preapproval',
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

        return array(
            array(
                'name' => 'merchantpreapprovalid',
                'external_name' => 'MerchantPreapprovalID',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'methodid',
                'external_name' => 'MethodID',
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
                'default' => 0,
                'value_source' => S2P_SDK_Values_Source::TYPE_RECURRING_METHODS,
            ),
            array(
                'name' => 'recurringperiod',
                'external_name' => 'RecurringPeriod',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'description',
                'external_name' => 'Description',
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^.{1,255}$',
                'default' => '',
            ),
            array(
                'name' => 'preapprovedmaximumamount',
                'external_name' => 'PreapprovedMaximumAmount',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'currency',
                'external_name' => 'Currency',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^[A-Z]{3}$',
                'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ),
            array(
                'name' => 'returnurl',
                'external_name' => 'ReturnURL',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
                'check_constant' => 'S2P_SDK_PAYMENT_RETURN_URL',
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
        );
    }

}