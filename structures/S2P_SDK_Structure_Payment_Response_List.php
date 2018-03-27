<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Payment_Response_List extends S2P_SDK_Structure_Payment_Response
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'payments_list',
            'external_name' => 'PaymentsList',
            'type' => S2P_SDK_VTYPE_BLOB_GROUP,
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
                'name' => 'payments',
                'external_name' => 'Payments',
                'type' => S2P_SDK_VTYPE_BLARRAY,
                'structure' => parent::get_structure_definition(),
                'default' => array(),
            ),
            array(
                'name' => 'total_pages',
                'external_name' => 'TotalPages',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
            ),
            array(
                'name' => 'page_size',
                'external_name' => 'PageSize',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
            ),
            array(
                'name' => 'page_index',
                'external_name' => 'PageIndex',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
            ),
            array(
                'name' => 'count',
                'external_name' => 'Count',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
            ),
            array(
                'name' => 'total_count',
                'external_name' => 'TotalCount',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
            ),
            array(
                'name' => 'error',
                'external_name' => 'Error',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
        );
    }

}
