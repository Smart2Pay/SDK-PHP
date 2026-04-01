<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Card_Payment_Request extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'payment',
            'external_name' => 'Payment',
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
        $token_details_obj = new S2P_SDK_Structure_Card_Token_Details();
        $td_secure_obj = new S2P_SDK_Structure_3D_Secure_Data();
        $device_info_obj = new S2P_SDK_Structure_Device_Info();
        $card_on_file_obj = new S2P_SDK_Structure_Card_On_File();
        $split_obj = new S2P_SDK_Structure_Split_Request();

        return [
            [
                'name'          => 'merchanttransactionid',
                'external_name' => 'MerchantTransactionID',
                'display_name'  => self::s2p_t('Payment merchant assigned transaction ID'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^[0-9a-zA-Z_-]{1,50}$',
            ],
            [
                'name'          => 'skinid',
                'external_name' => 'SkinID',
                'display_name'  => self::s2p_t('Skin ID to be used'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => null,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'originatortransactionid',
                'external_name' => 'OriginatorTransactionID',
                'display_name'  => self::s2p_t('A number that uniquely identifies the transaction in the original requester\'s system'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
                'regexp'        => '^[0-9a-zA-Z_-]{1,50}$',
            ],
            [
                'name'          => 'amount',
                'external_name' => 'Amount',
                'display_name'  => self::s2p_t('Payment amount'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'currency',
                'external_name' => 'Currency',
                'display_name'  => self::s2p_t('Payment currency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
                'regexp'        => '^[a-zA-Z]{3}$',
                'value_source'  => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ],
            [
                'name'           => 'returnurl',
                'external_name'  => 'ReturnURL',
                'display_name'   => self::s2p_t('Payment return URL'),
                'type'           => S2P_SDK_VTYPE_STRING,
                'default'        => '',
                'regexp'         => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
                'check_constant' => 'S2P_SDK_PAYMENT_RETURN_URL',
            ],
            [
                'name'          => 'description',
                'external_name' => 'Description',
                'display_name'  => self::s2p_t('Payment description'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^(.{1,500})?$',
            ],
            [
                'name'          => 'statementdescriptor',
                'external_name' => 'StatementDescriptor',
                'display_name'  => self::s2p_t('Payment statement description'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^(.{1,250})?$',
            ],
            [
                'name'          => 'billingaddress',
                'external_name' => 'BillingAddress',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $address_obj->get_structure_definition(),
            ],
            [
                'name'          => 'shippingaddress',
                'external_name' => 'ShippingAddress',
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
                'name'          => 'creditcardtoken',
                'external_name' => 'CreditCardToken',
                'display_name'  => self::s2p_t('Credit card token structure'),
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $token_details_obj->get_structure_definition(),
            ],
            [
                'name'          => 'installments',
                'external_name' => 'Installments',
                'display_name'  => self::s2p_t('Payment split into installments'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'splits',
                'external_name' => 'Splits',
                'type'          => S2P_SDK_VTYPE_BLARRAY,
                'default'       => null,
                'structure'     => $split_obj->get_structure_definition(),
            ],
            [
                'name'            => 'capture',
                'external_name'   => 'Capture',
                'display_name'    => self::s2p_t('Should capture payment?'),
                'type'            => S2P_SDK_VTYPE_BOOL,
                'default'         => null,
                'skip_if_default' => true,
            ],
            [
                'name'            => 'retry',
                'external_name'   => 'Retry',
                'display_name'    => self::s2p_t('Should retry payment?'),
                'type'            => S2P_SDK_VTYPE_BOOL,
                'default'         => null,
                'skip_if_default' => true,
            ],
            [
                'name'            => '3dsecure',
                'external_name'   => '3DSecure',
                'display_name'    => self::s2p_t('Should try a 3D secure payment?'),
                'type'            => S2P_SDK_VTYPE_BOOL,
                'default'         => null,
                'skip_if_default' => true,
            ],
            [
                'name'            => '3dsecuredata',
                'external_name'   => '3DSecureData',
                'type'            => S2P_SDK_VTYPE_BLOB,
                'default'         => null,
                'skip_if_default' => true,
                'structure'       => $td_secure_obj->get_structure_definition(),
            ],
            [
                'name'            => 'deviceinfo',
                'external_name'   => 'DeviceInfo',
                'type'            => S2P_SDK_VTYPE_BLOB,
                'default'         => null,
                'skip_if_default' => true,
                'structure'       => $device_info_obj->get_structure_definition(),
            ],
            [
                'name'            => 'scaexemption',
                'external_name'   => 'ScaExemption',
                'display_name'    => self::s2p_t('Sca exemption'),
                'type'            => S2P_SDK_VTYPE_STRING,
                'default'         => '',
                'skip_if_default' => true,
            ],
            [
                'name'            => 'cardonfile',
                'external_name'   => 'CardOnFile',
                'type'            => S2P_SDK_VTYPE_BLOB,
                'default'         => null,
                'skip_if_default' => true,
                'structure'       => $card_on_file_obj->get_structure_definition(),
            ],
            [
                'name'            => 'language',
                'external_name'   => 'Language',
                'display_name'    => self::s2p_t('Language used'),
                'type'            => S2P_SDK_VTYPE_STRING,
                'default'         => '',
                'skip_if_default' => true,
            ],
            [
                'name'            => 'generatecreditcardtoken',
                'external_name'   => 'GenerateCreditCardToken',
                'display_name'    => self::s2p_t('Should return credit card token in response?'),
                'type'            => S2P_SDK_VTYPE_BOOL,
                'default'         => null,
                'skip_if_default' => true,
            ],
            [
                'name'            => 'moto',
                'external_name'   => 'Moto',
                'display_name'    => self::s2p_t('If set to true, the payment will be marked at the acquirer as Mail Order Telephone Order type of transaction. This is not available for all acquirers.'),
                'type'            => S2P_SDK_VTYPE_BOOL,
                'default'         => null,
                'skip_if_default' => true,
            ],
            [
                'name'          => 'paymenttokenlifetime',
                'external_name' => 'PaymentTokenLifetime',
                'display_name'  => self::s2p_t('Payment token lifetime'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
        ];
    }
}
