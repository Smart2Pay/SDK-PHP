<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

class S2P_SDK_Structure_Payment_Details extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'details',
            'external_name' => 'Details',
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
                'name' => 'bankcode',
                'external_name' => 'BankCode',
                'display_name' => self::s2p_t( 'Payment bank code' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'accountnumber',
                'external_name' => 'AccountNumber',
                'display_name' => self::s2p_t( 'Payment account number' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'iban',
                'external_name' => 'IBAN',
                'display_name' => self::s2p_t( 'Payment IBAN' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'bic',
                'external_name' => 'BIC',
                'display_name' => self::s2p_t( 'Payment BIC' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'entityid',
                'external_name' => 'EntityID',
                'display_name' => self::s2p_t( 'Payment entity ID' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'referenceid',
                'external_name' => 'ReferenceID',
                'display_name' => self::s2p_t( 'Payment reference ID' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'entitynumber',
                'external_name' => 'EntityNumber',
                'display_name' => self::s2p_t( 'Payment entity number' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'referencenumber',
                'external_name' => 'ReferenceNumber',
                'display_name' => self::s2p_t( 'Payment reference number' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'accountholder',
                'external_name' => 'AccountHolder',
                'display_name' => self::s2p_t( 'Payment account holder' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'bankname',
                'external_name' => 'BankName',
                'display_name' => self::s2p_t( 'Payment bank name' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'swift_bic',
                'external_name' => 'SWIFT_BIC',
                'display_name' => self::s2p_t( 'Payment SWIFT / BIC' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'accountcurrency',
                'external_name' => 'AccountCurrency',
                'display_name' => self::s2p_t( 'Payment account currency' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'prepaidcard',
                'external_name' => 'PrepaidCard',
                'display_name' => self::s2p_t( 'Payment prepaid card' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'prepaidcardpin',
                'external_name' => 'PrepaidCardPIN',
                'display_name' => self::s2p_t( 'Payment prepaid card PIN' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'serialnumbers',
                'external_name' => 'SerialNumbers',
                'display_name' => self::s2p_t( 'Payment serial numbers' ),
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
            array(
                'name' => 'payercountry',
                'external_name' => 'PayerCountry',
                'display_name' => self::s2p_t( 'Payer country ISO 2 characters' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^[a-zA-Z]{2}$',
                'value_source' => S2P_SDK_Values_Source::TYPE_COUNTRY,
            ),
            array(
                'name' => 'payeremail',
                'external_name' => 'PayerEmail',
                'display_name' => self::s2p_t( 'Payer email address' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => S2P_SDK_Module::EMAIL_REGEXP,
            ),
            array(
                'name' => 'payerphone',
                'external_name' => 'PayerPhone',
                'display_name' => self::s2p_t( 'Payer phone number' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,20}$',
            ),
       );
    }

}
