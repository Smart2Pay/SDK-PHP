<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Card_Details extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'card',
            'external_name' => 'Card',
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
                'name'            => 'holdername',
                'external_name'   => 'HolderName',
                'display_name'    => self::s2p_t('Card holder name'),
                'type'            => S2P_SDK_VTYPE_STRING,
                'default'         => '',
                'skip_if_default' => true,
                'regexp'          => '^[^\d]*$',
            ],
            [
                'name'            => 'number',
                'external_name'   => 'Number',
                'display_name'    => self::s2p_t('Card number'),
                'type'            => S2P_SDK_VTYPE_STRING,
                'default'         => '',
                'skip_if_default' => true,
                'regexp'          => '^(\d{16,19})$',
            ],
            [
                'name'            => 'expirationmonth',
                'external_name'   => 'ExpirationMonth',
                'display_name'    => self::s2p_t('Card expiration month'),
                'type'            => S2P_SDK_VTYPE_STRING,
                'default'         => '',
                'skip_if_default' => true,
                'regexp'          => '^(0?[1-9]|1[0-2])$',
            ],
            [
                'name'            => 'expirationyear',
                'external_name'   => 'ExpirationYear',
                'display_name'    => self::s2p_t('Card expiration year'),
                'type'            => S2P_SDK_VTYPE_STRING,
                'default'         => '',
                'skip_if_default' => true,
                'regexp'          => '^(20|)([0-9]{2})$',
            ],
            [
                'name'            => 'issuingbankcountry',
                'external_name'   => 'IssuingBankCountry',
                'display_name'    => self::s2p_t('Card issuing bank country'),
                'type'            => S2P_SDK_VTYPE_STRING,
                'default'         => '',
                'skip_if_default' => true,
                'regexp'          => '^[a-zA-Z]{2}$',
                'value_source'    => S2P_SDK_Values_Source::TYPE_COUNTRY,
            ],
            [
                'name'            => 'securitycode',
                'external_name'   => 'SecurityCode',
                'display_name'    => self::s2p_t('Card security code (CVV2)'),
                'type'            => S2P_SDK_VTYPE_STRING,
                'default'         => '',
                'skip_if_default' => true,
                'regexp'          => '^([0-9]){3,4}$',
            ],
            [
                'name'            => 'requiresecuritycode',
                'external_name'   => 'RequireSecurityCode',
                'display_name'    => self::s2p_t('If set to false when the security code parameter is not used, it will not redirect to the form page to fill in the security code, but the payment will be directly sent to the payment provider.'),
                'type'            => S2P_SDK_VTYPE_BOOL,
                'default'         => null,
                'skip_if_default' => true,
            ],
            [
                'name'            => 'token',
                'external_name'   => 'Token',
                'display_name'    => self::s2p_t('Card token'),
                'type'            => S2P_SDK_VTYPE_STRING,
                'default'         => '',
                'skip_if_default' => true,
            ],
            [
                'name'            => 'maskednumber',
                'external_name'   => 'MaskedNumber',
                'display_name'    => self::s2p_t('Card masked number'),
                'type'            => S2P_SDK_VTYPE_STRING,
                'default'         => '',
                'skip_if_default' => true,
            ],
        ];
    }
}
