<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Merchant_Create_Response extends S2P_SDK_Scope_Structure
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
        $merchant_obj = new S2P_SDK_Structure_Merchant_Response();
        $user_obj = new S2P_SDK_Structure_User_Response();
        $site_obj = new S2P_SDK_Structure_Merchantsite();

        return [
            [
                'name'          => 'user',
                'external_name' => 'User',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $user_obj->get_structure_definition(),
            ],
            [
                'name'          => 'merchant',
                'external_name' => 'Merchant',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $merchant_obj->get_structure_definition(),
            ],
            [
                'name'          => 'merchant_site',
                'external_name' => 'MerchantSite',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $site_obj->get_structure_definition(),
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
                        'display_name'  => self::s2p_t('Status reason code'),
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => '',
                    ],
                    [
                        'name'          => 'info',
                        'external_name' => 'Info',
                        'display_name'  => self::s2p_t('Status reason info'),
                        'type'          => S2P_SDK_VTYPE_STRING,
                        'default'       => '',
                    ],
                ],
            ],
        ];
    }
}
