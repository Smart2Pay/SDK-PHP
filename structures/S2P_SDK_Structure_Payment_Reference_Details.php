<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Payment_Reference_Details extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'referencedetails',
            'external_name' => 'ReferenceDetails',
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
                'name'          => 'bankcode',
                'external_name' => 'BankCode',
                'display_name'  => self::s2p_t('Reference bank code'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'bankname',
                'external_name' => 'BankName',
                'display_name'  => self::s2p_t('Reference bank name'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'entityid',
                'external_name' => 'EntityID',
                'display_name'  => self::s2p_t('Reference entity ID'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'entitynumber',
                'external_name' => 'EntityNumber',
                'display_name'  => self::s2p_t('Reference entity number'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'referenceid',
                'external_name' => 'ReferenceID',
                'display_name'  => self::s2p_t('Reference ID'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'referencenumber',
                'external_name' => 'ReferenceNumber',
                'display_name'  => self::s2p_t('Reference number'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'swift_bic',
                'external_name' => 'SwiftBIC',
                'display_name'  => self::s2p_t('Reference SWIFT / BIC'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'accountcurrency',
                'external_name' => 'AccountCurrency',
                'display_name'  => self::s2p_t('Reference account currency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'accountnumber',
                'external_name' => 'AccountNumber',
                'display_name'  => self::s2p_t('Reference account number'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'accountholder',
                'external_name' => 'AccountHolder',
                'display_name'  => self::s2p_t('Reference account holder'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'iban',
                'external_name' => 'IBAN',
                'display_name'  => self::s2p_t('Reference IBAN'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'amounttopay',
                'external_name' => 'AmountToPay',
                'display_name'  => self::s2p_t('Amount to be paid'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'qrcodeurl',
                'external_name' => 'QRCodeURL',
                'display_name'  => self::s2p_t('QRCode URL'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'boletourl',
                'external_name' => 'BoletoURL',
                'display_name'  => self::s2p_t('Boleto URL'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'instructions',
                'external_name' => 'Instructions',
                'display_name'  => self::s2p_t('Payment Instructions'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
        ];
    }
}
