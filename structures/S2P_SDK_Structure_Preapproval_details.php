<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Preapproval_details extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'preapprovaldetails',
            'external_name' => 'PreapprovalDetails',
            'type' => S2P_SDK_VTYPE_BLOB,
            'structure' => $this->get_structure_definition(),
        );
    }

    /**
     * Function should return structure definition for blobs or array variables
     * @return array
     */
    public function get_structure_definition()
    {
        return array(
            array(
                'name' => 'preapprovedmaximumamount',
                'external_name' => 'PreapprovedMaximumAmount',
                'display_name' => self::s2p_t( 'Preapproved maximum amount' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'merchantpreapprovalid',
                'external_name' => 'MerchantPreapprovalID',
                'display_name' => self::s2p_t( 'Preapproval id provided by merchant' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'frequency',
                'external_name' => 'Frequency',
                'display_name' => self::s2p_t( 'Preapproval frequency' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'value_source' => S2P_SDK_Values_Source::TYPE_PREAPPROVAL_FREQUENCY,
            ),
            array(
                'name' => 'preapprovaldescription',
                'external_name' => 'PreapprovalDescription',
                'display_name' => self::s2p_t( 'Preapproval description' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
        );
    }

}
