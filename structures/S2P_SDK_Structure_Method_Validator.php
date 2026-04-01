<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Method_Validator extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'validator',
            'external_name' => 'Validator',
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
                'name'          => 'source',
                'external_name' => 'Source',
                'display_name'  => self::s2p_t('Validator source'),
                'type'          => S2P_SDK_VTYPE_STRING,
            ],
            [
                'name'          => 'regex',
                'external_name' => 'Regex',
                'display_name'  => self::s2p_t('Validator regular expression'),
                'type'          => S2P_SDK_VTYPE_STRING,
            ],
            [
                'name'          => 'required',
                'external_name' => 'Required',
                'display_name'  => self::s2p_t('Validator is mandatory'),
                'type'          => S2P_SDK_VTYPE_BOOL,
            ],
        ];
    }
}
