<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Refund_Details extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'refunddetails',
            'external_name' => 'RefundDetails',
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
                'name'          => 'customeraccountnumber',
                'external_name' => 'CustomerAccountNumber',
                'display_name'  => self::s2p_t('Refund customer account number'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'cpfaccountholder',
                'external_name' => 'CPFAccountHolder',
                'display_name'  => self::s2p_t('Refund CPF account holder'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'bankname',
                'external_name' => 'BankName',
                'display_name'  => self::s2p_t('Refund bank name'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'bankaccounttype',
                'external_name' => 'BankAccountType',
                'display_name'  => self::s2p_t('Refund bank account type'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'bankbranch',
                'external_name' => 'BankBranch',
                'display_name'  => self::s2p_t('Refund bank branch'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'bankcode',
                'external_name' => 'BankCode',
                'display_name'  => self::s2p_t('Refund bank code'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'bankagencycode',
                'external_name' => 'BankAgencyCode',
                'display_name'  => self::s2p_t('Refund bank agency code'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'bankaccountnumber',
                'external_name' => 'BankAccountNumber',
                'display_name'  => self::s2p_t('Refund account number'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'bankswiftid',
                'external_name' => 'BankSWIFTID',
                'display_name'  => self::s2p_t('Refund bank SWIFT ID'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'banksortcode',
                'external_name' => 'BankSortCode',
                'display_name'  => self::s2p_t('Refund bank sort code'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'customeriban',
                'external_name' => 'CustomerIBAN',
                'display_name'  => self::s2p_t('Refund customer IBAN'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
        ];
    }
}
