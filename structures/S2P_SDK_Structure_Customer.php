<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Customer extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'customer',
            'external_name' => 'Customer',
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
                'name'            => 'id',
                'external_name'   => 'ID',
                'display_name'    => self::s2p_t('Customer ID'),
                'type'            => S2P_SDK_VTYPE_INT,
                'regexp'          => '^\d{1,12}$',
                'default'         => 0,
                'skip_if_default' => true,
            ],
            [
                'name'          => 'merchantcustomerid',
                'external_name' => 'MerchantCustomerID',
                'display_name'  => self::s2p_t('Merchant assigned customer ID'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
                'regexp'        => '^([0-9a-zA-Z_-]{1,50})?$',
            ],
            [
                'name'          => 'email',
                'external_name' => 'Email',
                'display_name'  => self::s2p_t('Customer email'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
                'regexp'        => S2P_SDK_Module::EMAIL_REGEXP,
            ],
            [
                'name'          => 'firstname',
                'external_name' => 'FirstName',
                'display_name'  => self::s2p_t('Customer first name'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
            ],
            [
                'name'          => 'lastname',
                'external_name' => 'LastName',
                'display_name'  => self::s2p_t('Customer last name'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
            ],
            [
                'name'          => 'gender',
                'external_name' => 'Gender',
                'display_name'  => self::s2p_t('Customer gender'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
            ],
            [
                'name'          => 'dateofbirth',
                'external_name' => 'DateOfBirth',
                'display_name'  => self::s2p_t('Customer date of birth'),
                'type'          => S2P_SDK_VTYPE_DATE,
                'default'       => null,
                'regexp'        => '^(((19|20)\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01]))$',
            ],
            [
                'name'          => 'socialsecuritynumber',
                'external_name' => 'SocialSecurityNumber',
                'display_name'  => self::s2p_t('Customer social security number'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
            ],
            [
                'name'          => 'phone',
                'external_name' => 'Phone',
                'display_name'  => self::s2p_t('Customer phone'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
            ],
            [
                'name'          => 'company',
                'external_name' => 'Company',
                'display_name'  => self::s2p_t('Customer company'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => null,
            ],
            [
                'name'            => 'inputdatetime',
                'external_name'   => 'InputDateTime',
                'display_name'    => self::s2p_t('Customer creation date'),
                'type'            => S2P_SDK_VTYPE_DATETIME,
                'default'         => null,
                'skip_if_default' => true,
            ],
        ];
    }
}
