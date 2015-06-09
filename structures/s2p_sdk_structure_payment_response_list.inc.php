<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_response.inc.php' );

class S2P_SDK_Structure_Payment_Response_List extends S2P_SDK_Structure_Payment_Response
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'payments',
            'external_name' => 'Payments',
            'type' => S2P_SDK_VTYPE_BLARRAY,
            'structure' => $this->get_structure_definition(),
            'default' => null,
        );
    }

}