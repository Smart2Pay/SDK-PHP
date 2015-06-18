<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_customer.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_address.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_structure_status.inc.php' );

class S2P_SDK_Structure_Preapproval_Response extends S2P_SDK_Scope_Structure
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
        $status_obj = new S2P_SDK_Structure_Status();

        return array(
            array(
                'name' => 'id',
                'external_name' => 'ID',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'created',
                'external_name' => 'Created',
                'type' => S2P_SDK_VTYPE_DATETIME,
                'default' => '',
            ),
            array(
                'name' => 'methodid',
                'external_name' => 'MethodID',
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
                'default' => 0,
            ),
            array(
                'name' => 'siteid',
                'external_name' => 'SiteID',
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
                'default' => 0,
            ),
            array(
                'name' => 'merchantpreapprovalid',
                'external_name' => 'MerchantPreapprovalID',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'recurringperiod',
                'external_name' => 'RecurringPeriod',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
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
            ),
            array(
                'name' => 'returnurl',
                'external_name' => 'ReturnURL',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
            ),
            array(
                'name' => 'description',
                'external_name' => 'Description',
                'type' => S2P_SDK_VTYPE_STRING,
                'regexp' => '^.{1,255}$',
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
                'name' => 'status',
                'external_name' => 'Status',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $status_obj->get_structure_definition(),
            ),
        );
    }

}