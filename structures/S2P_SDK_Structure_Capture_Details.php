<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Capture_Details extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'capturedetails',
            'external_name' => 'CaptureDetails',
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
        $status_obj = new S2P_SDK_Structure_Status();

        return array(
            array(
                'name' => 'id',
                'external_name' => 'ID',
                'display_name' => self::s2p_t( 'Capture ID' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'amount',
                'external_name' => 'Amount',
                'display_name' => self::s2p_t( 'Capture amount' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,12}$',
            ),
            array(
                'name' => 'status',
                'external_name' => 'Status',
                'display_name' => self::s2p_t( 'Preapproval ID' ),
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $status_obj->get_structure_definition(),
            ),
        );
    }

}
