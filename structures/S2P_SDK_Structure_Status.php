<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Status extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'status',
            'external_name' => 'Status',
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
                'display_name'  => self::s2p_t('Status ID'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
            ],
            [
                'name'          => 'info',
                'external_name' => 'Info',
                'display_name'  => self::s2p_t('Status info'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
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
                        'type'          => S2P_SDK_VTYPE_INT,
                        'default'       => 0,
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
