<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Merchant_Response extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'merchant',
            'external_name' => 'Merchant',
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
                'name'          => 'id',
                'external_name' => 'ID',
                'display_name'  => self::s2p_t('Merchant ID'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'alias',
                'external_name' => 'Alias',
                'display_name'  => self::s2p_t('Merchant alias'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,255}$',
            ],
            [
                'name'          => 'created',
                'external_name' => 'Created',
                'display_name'  => self::s2p_t('Date and time of creation'),
                'type'          => S2P_SDK_VTYPE_DATETIME,
            ],
            [
                'name'          => 'active',
                'external_name' => 'Active',
                'display_name'  => self::s2p_t('Merchant status'),
                'type'          => S2P_SDK_VTYPE_BOOL,
                'default'       => true,
            ],
            [
                'name'          => 'provider_merchant_id',
                'external_name' => 'ProviderMerchantID',
                'display_name'  => self::s2p_t('ID of merchant provider'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
                'default'       => 0,
            ],
            [
                'name'          => 'required_site_id',
                'external_name' => 'RequiredSiteID',
                'display_name'  => self::s2p_t('Requested site ID'),
                'type'          => S2P_SDK_VTYPE_BOOL,
                'default'       => false,
            ],
            [
                'name'          => 'reasons',
                'external_name' => 'Reasons',
                'type'          => S2P_SDK_VTYPE_BLARRAY,
                'default'       => null,
                'structure'     => [
                    [
                        'name'          => 'code',
                        'external_name' => 'Code',
                        'display_name'  => self::s2p_t('Reason code'),
                        'type'          => S2P_SDK_VTYPE_INT,
                        'default'       => 0,
                    ],
                    [
                        'name'          => 'info',
                        'external_name' => 'Info',
                        'display_name'  => self::s2p_t('Reason info'),
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => '',
                    ],
                ],
            ],
        ];
    }
}
