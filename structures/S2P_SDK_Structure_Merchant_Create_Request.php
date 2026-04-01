<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Merchant_Create_Request extends S2P_SDK_Scope_Structure
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
        $site_obj = new S2P_SDK_Structure_Merchantsite();
        $merchant_obj = new S2P_SDK_Structure_Merchant_Request();
        $user_obj = new S2P_SDK_Structure_User_Request();

        return [
            [
                'name'          => 'company_name',
                'external_name' => 'CompanyName',
                'display_name'  => self::s2p_t('Merchant company name'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^.{1,150}$',
            ],
            [
                'name'          => 'company_address',
                'external_name' => 'CompanyAddress',
                'display_name'  => self::s2p_t('Merchant company address'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^.{1,255}$',
            ],
            [
                'name'          => 'merchant',
                'external_name' => 'Merchant',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $merchant_obj->get_structure_definition(),
            ],
            [
                'name'          => 'user',
                'external_name' => 'User',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $user_obj->get_structure_definition(),
            ],
            [
                'name'          => 'merchant_site',
                'external_name' => 'MerchantSite',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $site_obj->get_structure_definition(),
            ],
        ];
    }
}
