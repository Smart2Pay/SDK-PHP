<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Payment_Customer_Details extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'customerdetails',
            'external_name' => 'CustomerDetails',
            'type'          => S2P_SDK_VTYPE_BLOB,
            'structure'     => $this->get_structure_definition(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_structure_definition() : array
    {
        return [
            [
                'name'          => 'accountnumber',
                'external_name' => 'AccountNumber',
                'display_name'  => self::s2p_t('Customer account number'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'accountholder',
                'external_name' => 'AccountHolder',
                'display_name'  => self::s2p_t('Customer account holder'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'iban',
                'external_name' => 'IBAN',
                'display_name'  => self::s2p_t('Customer IBAN'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'bic',
                'external_name' => 'BIC',
                'display_name'  => self::s2p_t('Customer BIC'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'prepaidcard',
                'external_name' => 'PrepaidCard',
                'display_name'  => self::s2p_t('Customer prepaid card'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'prepaidcardpin',
                'external_name' => 'PrepaidCardPIN',
                'display_name'  => self::s2p_t('Customer prepaid card PIN'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'serialnumbers',
                'external_name' => 'SerialNumbers',
                'display_name'  => self::s2p_t('Customer serial numbers'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'wallet',
                'external_name' => 'Wallet',
                'display_name'  => self::s2p_t('Customer wallet'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'payercountry',
                'external_name' => 'PayerCountry',
                'display_name'  => self::s2p_t('Payer country ISO 2 characters'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^[a-zA-Z]{2}$',
                'value_source'  => S2P_SDK_Values_Source::TYPE_COUNTRY,
            ],
            [
                'name'          => 'payeremail',
                'external_name' => 'PayerEmail',
                'display_name'  => self::s2p_t('Payer email address'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => S2P_SDK_Module::EMAIL_REGEXP,
            ],
            [
                'name'          => 'payerphone',
                'external_name' => 'PayerPhone',
                'display_name'  => self::s2p_t('Payer phone number'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^.{1,20}$',
            ],
        ];
    }
}
