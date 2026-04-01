<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Generic_Error extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'message',
            'external_name' => 'Message',
            'type'          => S2P_SDK_VTYPE_STRING,
            'default'       => '',
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_structure_definition() : array
    {
        return [];
    }
}
