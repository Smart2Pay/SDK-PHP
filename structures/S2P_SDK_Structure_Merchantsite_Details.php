<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Merchantsite_Details extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'merchantsitedetails',
            'external_name' => 'MerchantSiteDetails',
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
                'name'          => 'reasons',
                'external_name' => 'Reasons',
                'type'          => S2P_SDK_VTYPE_BLARRAY,
                'default'       => null,
                'display_name'  => self::s2p_t('Possible error codes'),
                'structure'     => [
                    [
                        'name'          => 'code',
                        'external_name' => 'Code',
                        'type'          => S2P_SDK_VTYPE_INT,
                        'default'       => 0,
                        'display_name'  => self::s2p_t('Error code'),
                    ],
                    [
                        'name'          => 'info',
                        'external_name' => 'Info',
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => '',
                        'display_name'  => self::s2p_t('Error message'),
                    ],
                ],
            ],
            [
                'name'          => 'name',
                'external_name' => 'Name',
                'display_name'  => self::s2p_t('Site Name'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,50}$',
            ],
            [
                'name'          => 'country',
                'external_name' => 'Country',
                'display_name'  => self::s2p_t('Site Country'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^[a-zA-Z]{2}$',
            ],
            [
                'name'          => 'city',
                'external_name' => 'City',
                'display_name'  => self::s2p_t('Site City'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,50}$',
            ],
            [
                'name'          => 'email',
                'external_name' => 'Email',
                'display_name'  => self::s2p_t('Site contact email address'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => S2P_SDK_Module::EMAIL_REGEXP,
            ],
            [
                'name'          => 'address',
                'external_name' => 'Address',
                'display_name'  => self::s2p_t('Site contact address'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,512}$',
            ],
            [
                'name'          => 'bankname',
                'external_name' => 'BankName',
                'display_name'  => self::s2p_t('Site bank name'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,50}$',
            ],
            [
                'name'          => 'accountiban',
                'external_name' => 'AccountIBAN',
                'display_name'  => self::s2p_t('Site account IBAN'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16}$',
            ],
            [
                'name'          => 'accountswift',
                'external_name' => 'AccountSWIFT',
                'display_name'  => self::s2p_t('Site account SWIFT'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^\w{1,30}$',
            ],
            [
                'name'          => 'bankswiftid',
                'external_name' => 'BankSWIFTID',
                'display_name'  => self::s2p_t('Site bank SWIFT'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^[a-zA-Z]{6}[a-zA-Z0-9]{2}([a-zA-Z0-9]{3})?$',
            ],
            [
                'name'          => 'bankcode',
                'external_name' => 'BankCode',
                'display_name'  => self::s2p_t('Site bank code'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^[a-zA-Z]{4}[a-zA-Z]{2}[a-zA-Z0-9]{2}[XXX0-9]{0,3}',
            ],
            [
                'name'          => 'vatnumber',
                'external_name' => 'VATNumber',
                'display_name'  => self::s2p_t('Site VAT number'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,50}$',
            ],
            [
                'name'          => 'registrationnumber',
                'external_name' => 'RegistrationNumber',
                'display_name'  => self::s2p_t('Site registration number'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,50}$',
            ],
            [
                'name'          => 'mcc',
                'external_name' => 'MCC',
                'display_name'  => self::s2p_t('Site company MCC'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^[0-9]{1,10}$',
            ],
            [
                'name'          => 'main_business',
                'external_name' => 'MainBusiness',
                'display_name'  => self::s2p_t('Site company main business'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,100}$',
            ],
        ];
    }
}
