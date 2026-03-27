<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Preapproval_details extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'preapprovaldetails',
            'external_name' => 'PreapprovalDetails',
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
                'name'          => 'preapprovedmaximumamount',
                'external_name' => 'PreapprovedMaximumAmount',
                'display_name'  => self::s2p_t('Preapproved maximum amount'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'merchantpreapprovalid',
                'external_name' => 'MerchantPreapprovalID',
                'display_name'  => self::s2p_t('Preapproval id provided by merchant'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'frequency',
                'external_name' => 'Frequency',
                'display_name'  => self::s2p_t('Preapproval frequency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'value_source'  => S2P_SDK_Values_Source::TYPE_PREAPPROVAL_FREQUENCY,
            ],
            [
                'name'          => 'preapprovaldescription',
                'external_name' => 'PreapprovalDescription',
                'display_name'  => self::s2p_t('Preapproval description'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
        ];
    }
}
