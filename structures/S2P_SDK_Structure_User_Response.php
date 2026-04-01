<?php
namespace S2P_SDK;

class S2P_SDK_Structure_User_Response extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'user',
            'external_name' => 'User',
            'type'          => S2P_SDK_VTYPE_BLOB,
            'structure'     => $this->get_structure_definition(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_structure_definition() : array
    {
        $status_obj = new S2P_SDK_Structure_Status();

        return [
            [
                'name'          => 'id',
                'external_name' => 'ID',
                'display_name'  => self::s2p_t('User ID'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'name',
                'external_name' => 'Name',
                'display_name'  => self::s2p_t('Account username'),
                'type'          => S2P_SDK_VTYPE_STRING,
            ],
            [
                'name'          => 'password',
                'external_name' => 'Password',
                'display_name'  => self::s2p_t('Account password'),
                'type'          => S2P_SDK_VTYPE_STRING,
            ],
            [
                'name'          => 'email',
                'external_name' => 'Email',
                'display_name'  => self::s2p_t('Account email'),
                'type'          => S2P_SDK_VTYPE_STRING,
            ],
            [
                'name'          => 'details',
                'external_name' => 'Details',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $status_obj->get_structure_definition(),
            ],
            [
                'name'          => 'siteid',
                'external_name' => 'SiteID',
                'display_name'  => self::s2p_t('Site ID attached to this account'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'roleid',
                'external_name' => 'RoleID',
                'display_name'  => self::s2p_t('Role ID assigned to user account'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
            ],
        ];
    }
}
