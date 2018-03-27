<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Merchant_Create_Request extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'merchant',
            'external_name' => 'Merchant',
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
        $site_obj = new S2P_SDK_Structure_Merchantsite();
        $merchant_obj = new S2P_SDK_Structure_Merchant_Request();
        $user_obj = new S2P_SDK_Structure_User_Request();

        return array(
            array(
                'name' => 'company_name',
                'external_name' => 'CompanyName',
                'display_name' => self::s2p_t( 'Merchant company name' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,150}$',
            ),
            array(
                'name' => 'company_address',
                'external_name' => 'CompanyAddress',
                'display_name' => self::s2p_t( 'Merchant company address' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,255}$',
            ),
            array(
                'name' => 'merchant',
                'external_name' => 'Merchant',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $merchant_obj->get_structure_definition()
            ),
            array(
                'name' => 'user',
                'external_name' => 'User',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $user_obj->get_structure_definition()
            ),
            array(
                'name' => 'merchant_site',
                'external_name' => 'MerchantSite',
                'type' => S2P_SDK_VTYPE_BLOB,
                'default' => null,
                'structure' => $site_obj->get_structure_definition()
            ),
       );
    }

}
