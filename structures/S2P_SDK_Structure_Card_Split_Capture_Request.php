<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Card_Split_Capture_Request extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'payment',
            'external_name' => 'Payment',
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
        $split_obj = new S2P_SDK_Structure_Split_Capture_Request();

        return array(
            array(
                'name' => 'split',
                'external_name' => 'Split',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $split_obj->get_structure_definition(),
            ),
            array(
                'name' => 'totalcapturecount',
                'external_name' => 'TotalCaptureCount',
                'display_name' => self::s2p_t( 'Total number of captures that will be performed on this split' ),
                'type' => S2P_SDK_VTYPE_INT,
                'regexp' => '^\d{1,12}$',
            ),
        );
    }

}
