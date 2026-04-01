<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Method_List extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'methods',
            'external_name' => 'Methods',
            'type'          => S2P_SDK_VTYPE_BLARRAY,
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
                'display_name'  => self::s2p_t('Method ID'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'displayname',
                'external_name' => 'DisplayName',
                'display_name'  => self::s2p_t('Method display name'),
                'type'          => S2P_SDK_VTYPE_STRING,
            ],
            [
                'name'          => 'description',
                'external_name' => 'Description',
                'display_name'  => self::s2p_t('Method description'),
                'type'          => S2P_SDK_VTYPE_STRING,
            ],
            [
                'name'          => 'logourl',
                'external_name' => 'LogoURL',
                'display_name'  => self::s2p_t('Method logo URL'),
                'type'          => S2P_SDK_VTYPE_STRING,
            ],
            [
                'name'          => 'guaranteed',
                'external_name' => 'Guaranteed',
                'display_name'  => self::s2p_t('Method is guaranteed'),
                'type'          => S2P_SDK_VTYPE_BOOL,
            ],
            [
                'name'          => 'active',
                'external_name' => 'Active',
                'display_name'  => self::s2p_t('Method status'),
                'type'          => S2P_SDK_VTYPE_BOOL,
            ],
            [
                'name'          => 'supportiframe',
                'external_name' => 'SupportIframe',
                'display_name'  => self::s2p_t('Method supports IFrame'),
                'type'          => S2P_SDK_VTYPE_BOOL,
            ],
            [
                'name'          => 'iframewidth',
                'external_name' => 'IframeWidth',
                'display_name'  => self::s2p_t('Method IFrame width'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
            ],
            [
                'name'          => 'iframeheight',
                'external_name' => 'IframeHeight',
                'display_name'  => self::s2p_t('Method IFrame height'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
            ],
            [
                'name'          => 'countries',
                'external_name' => 'Countries',
                'display_name'  => self::s2p_t('Method supported countries'),
                'type'          => S2P_SDK_VTYPE_ARRAY,
                'array_type'    => S2P_SDK_VTYPE_STRING,
                'default'       => [],
            ],
            [
                'name'          => 'currencies',
                'external_name' => 'Currencies',
                'display_name'  => self::s2p_t('Method supported currencies'),
                'type'          => S2P_SDK_VTYPE_ARRAY,
                'array_type'    => S2P_SDK_VTYPE_STRING,
                'default'       => [],
            ],
            [
                'name'          => 'detailsurl',
                'external_name' => 'DetailsURL',
                'display_name'  => self::s2p_t('Method details URL'),
                'type'          => S2P_SDK_VTYPE_STRING,
            ],
        ];
    }
}
