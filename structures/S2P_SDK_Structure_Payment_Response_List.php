<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Payment_Response_List extends S2P_SDK_Structure_Payment_Response
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'payments_list',
            'external_name' => 'PaymentsList',
            'type'          => S2P_SDK_VTYPE_BLOB_GROUP,
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
                'name'          => 'payments',
                'external_name' => 'Payments',
                'type'          => S2P_SDK_VTYPE_BLARRAY,
                'structure'     => parent::get_structure_definition(),
                'default'       => [],
            ],
            [
                'name'          => 'total_pages',
                'external_name' => 'TotalPages',
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
            ],
            [
                'name'          => 'page_size',
                'external_name' => 'PageSize',
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
            ],
            [
                'name'          => 'page_index',
                'external_name' => 'PageIndex',
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
            ],
            [
                'name'          => 'count',
                'external_name' => 'Count',
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
            ],
            [
                'name'          => 'total_count',
                'external_name' => 'TotalCount',
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
            ],
            [
                'name'          => 'error',
                'external_name' => 'Error',
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
        ];
    }
}
