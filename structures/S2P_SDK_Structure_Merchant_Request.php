<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Merchant_Request extends S2P_SDK_Scope_Structure
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
                'name'          => 'alias',
                'external_name' => 'Alias',
                'display_name'  => self::s2p_t('Merchant alias'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,255}$',
            ],
            [
                'name'          => 'active',
                'external_name' => 'Active',
                'display_name'  => self::s2p_t('Merchant status'),
                'type'          => S2P_SDK_VTYPE_BOOL,
            ],
            [
                'name'          => 'provider_merchant_id',
                'external_name' => 'ProviderMerchantID',
                'display_name'  => self::s2p_t('ID of merchant provider'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'required_site_id',
                'external_name' => 'RequiredSiteID',
                'display_name'  => self::s2p_t('Requested site ID'),
                'type'          => S2P_SDK_VTYPE_BOOL,
            ],
        ];
    }
}
