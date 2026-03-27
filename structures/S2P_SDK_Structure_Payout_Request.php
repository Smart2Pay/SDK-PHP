<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Payout_Request extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'payout',
            'external_name' => 'Payout',
            'type'          => S2P_SDK_VTYPE_BLOB,
            'structure'     => $this->get_structure_definition(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_structure_definition() : array
    {
        $customer_obj = new S2P_SDK_Structure_Customer();
        $address_obj = new S2P_SDK_Structure_Address();
        $card_details_obj = new S2P_SDK_Structure_Card_Details();

        return [
            [
                'name'          => 'merchanttransactionid',
                'external_name' => 'MerchantTransactionID',
                'display_name'  => self::s2p_t('Payout merchant assigned transaction ID'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^[0-9a-zA-Z_-]{1,50}$',
            ],
            [
                'name'          => 'amount',
                'external_name' => 'Amount',
                'display_name'  => self::s2p_t('Payout amount'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'currency',
                'external_name' => 'Currency',
                'display_name'  => self::s2p_t('Payout currency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
                'regexp'        => '^[A-Z]{3}$',
                'value_source'  => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ],
            [
                'name'          => 'description',
                'external_name' => 'Description',
                'display_name'  => self::s2p_t('Payout description'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,255}$',
            ],
            [
                'name'          => 'methodid',
                'external_name' => 'MethodID',
                'type'          => S2P_SDK_VTYPE_INT,
                'display_name'  => self::s2p_t('Payment method ID'),
                'regexp'        => '^\d{1,12}$',
                'default'       => 0,
            ],
            [
                'name'          => 'statementdescriptor',
                'external_name' => 'StatementDescriptor',
                'display_name'  => self::s2p_t('Payout statement description'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,255}$',
            ],
            [
                'name'          => 'billingaddress',
                'external_name' => 'BillingAddress',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $address_obj->get_structure_definition(),
            ],
            [
                'name'          => 'customer',
                'external_name' => 'Customer',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $customer_obj->get_structure_definition(),
            ],
            [
                'name'          => 'card',
                'external_name' => 'Card',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $card_details_obj->get_structure_definition(),
            ],
            [
                'name'          => 'details',
                'external_name' => 'Details',
                'type'          => S2P_SDK_VTYPE_BLARRAY,
                'default'       => null,
                'structure'     => [
                    [
                        'name'          => 'customeriban',
                        'external_name' => 'CustomerIBAN',
                        'display_name'  => self::s2p_t('Customer IBAN'),
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => '',
                    ],
                    [
                        'name'          => 'customerbankaccountid',
                        'external_name' => 'CustomerBankAccountID',
                        'display_name'  => self::s2p_t('Customer bank account id'),
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => '',
                    ],
                    [
                        'name'          => 'cryptoaddress',
                        'external_name' => 'CryptoAddress',
                        'display_name'  => self::s2p_t('Address when paying with crypto'),
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => '',
                    ],
                    [
                        'name'          => 'cryptocurrency',
                        'external_name' => 'CryptoCurrency',
                        'display_name'  => self::s2p_t('Crypto currency'),
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => null,
                        'regexp'        => '^[A-Z]{3}$',
                        'value_source'  => S2P_SDK_Values_Source::TYPE_CURRENCY,
                    ],
                    [
                        'name'          => 'Securityquestion',
                        'external_name' => 'SecurityQuestion',
                        'display_name'  => self::s2p_t('Security question'),
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => '',
                    ],
                    [
                        'name'          => 'securityanswer',
                        'external_name' => 'SecurityAnswer',
                        'display_name'  => self::s2p_t('Security answer'),
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => '',
                    ],
                    [
                        'name'          => 'ipaddress',
                        'external_name' => 'IPAddress',
                        'display_name'  => self::s2p_t('IP Address'),
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => '',
                        'regexp'        => '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$',
                    ],
                ],
            ],
        ];
    }
}
