<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Exchangerate_Response extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'exchangerate',
            'external_name' => 'ExchangeRate',
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
                'name'          => 'from',
                'external_name' => 'From',
                'display_name'  => self::s2p_t('From currency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
                'regexp'        => '^[a-zA-Z]{3}$',
                'value_source'  => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ],
            [
                'name'          => 'to',
                'external_name' => 'To',
                'display_name'  => self::s2p_t('To currency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
                'regexp'        => '^[a-zA-Z]{3}$',
                'value_source'  => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ],
            [
                'name'          => 'datetime',
                'external_name' => 'DateTime',
                'display_name'  => self::s2p_t('Last update date and time'),
                'type'          => S2P_SDK_VTYPE_DATETIME,
            ],
            [
                'name'          => 'rate',
                'external_name' => 'Rate',
                'display_name'  => self::s2p_t('Conversion rate'),
                'type'          => S2P_SDK_VTYPE_FLOAT,
            ],
        ];
    }
}
