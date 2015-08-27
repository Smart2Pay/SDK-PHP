<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

class S2P_SDK_Structure_Payment_Customer_Details extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'customerdetails',
            'external_name' => 'CustomerDetails',
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
                'name' => 'accountnumber',
                'external_name' => 'AccountNumber',
                'display_name' => self::s2p_t( 'Customer account number' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'accountholder',
                'external_name' => 'AccountHolder',
                'display_name' => self::s2p_t( 'Customer account holder' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'iban',
                'external_name' => 'IBAN',
                'display_name' => self::s2p_t( 'Customer IBAN' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'bic',
                'external_name' => 'BIC',
                'display_name' => self::s2p_t( 'Customer BIC' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'prepaidcard',
                'external_name' => 'PrepaidCard',
                'display_name' => self::s2p_t( 'Customer prepaid card' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'prepaidcardpin',
                'external_name' => 'PrepaidCardPIN',
                'display_name' => self::s2p_t( 'Customer prepaid card PIN' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'serialnumbers',
                'external_name' => 'SerialNumbers',
                'display_name' => self::s2p_t( 'Customer serial numbers' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'wallet',
                'external_name' => 'Wallet',
                'display_name' => self::s2p_t( 'Customer wallet' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
       );
    }

}
