<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Preapproval_Response extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'preapproval',
            'external_name' => 'Preapproval',
            'type'          => S2P_SDK_VTYPE_BLOB,
            'structure'     => $this->get_structure_definition(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_structure_definition() : array
    {
        $customer_obj = new S2P_SDK_Structure_Customer();
        $address_obj = new S2P_SDK_Structure_Address();
        $status_obj = new S2P_SDK_Structure_Status();

        return [
            [
                'name'          => 'id',
                'external_name' => 'ID',
                'display_name'  => self::s2p_t('Preapproval ID'),
                'type'          => S2P_SDK_VTYPE_LONG,
                'default'       => 0,
                'regexp'        => '^\d{1,19}$',
            ],
            [
                'name'          => 'created',
                'external_name' => 'Created',
                'display_name'  => self::s2p_t('Preapproval creation date and time'),
                'type'          => S2P_SDK_VTYPE_DATETIME,
                'default'       => '',
            ],
            [
                'name'          => 'methodid',
                'external_name' => 'MethodID',
                'display_name'  => self::s2p_t('Preapproval method ID'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
                'default'       => 0,
            ],
            [
                'name'          => 'siteid',
                'external_name' => 'SiteID',
                'display_name'  => self::s2p_t('Preapproval site ID'),
                'type'          => S2P_SDK_VTYPE_INT,
                'regexp'        => '^\d{1,12}$',
                'default'       => 0,
            ],
            [
                'name'          => 'merchantpreapprovalid',
                'external_name' => 'MerchantPreapprovalID',
                'display_name'  => self::s2p_t('Preapproval merchant assigned ID'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'recurringperiod',
                'external_name' => 'RecurringPeriod',
                'display_name'  => self::s2p_t('Preapproval recurring period'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'preapprovedmaximumamount',
                'external_name' => 'PreapprovedMaximumAmount',
                'display_name'  => self::s2p_t('Preapproval maximum amount'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^\d{1,12}$',
            ],
            [
                'name'          => 'currency',
                'external_name' => 'Currency',
                'display_name'  => self::s2p_t('Preapproval currency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^[A-Z]{3}$',
            ],
            [
                'name'          => 'returnurl',
                'external_name' => 'ReturnURL',
                'display_name'  => self::s2p_t('Preapproval return URL'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
            ],
            [
                'name'          => 'description',
                'external_name' => 'Description',
                'display_name'  => self::s2p_t('Preapproval description'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,255}$',
                'default'       => '',
            ],
            [
                'name'          => 'customer',
                'external_name' => 'Customer',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $customer_obj->get_structure_definition(),
            ],
            [
                'name'          => 'billingaddress',
                'external_name' => 'BillingAddress',
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $address_obj->get_structure_definition(),
            ],
            [
                'name'          => 'status',
                'external_name' => 'Status',
                'display_name'  => self::s2p_t('Preapproval ID'),
                'type'          => S2P_SDK_VTYPE_BLOB,
                'default'       => null,
                'structure'     => $status_obj->get_structure_definition(),
            ],
            [
                'name'          => 'redirecturl',
                'external_name' => 'RedirectURL',
                'display_name'  => self::s2p_t('Preapproval redirect URL'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'regexp'        => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
            ],
            [
                'name'          => 'methodoptionid',
                'external_name' => 'MethodOptionID',
                'display_name'  => self::s2p_t('Option for Payment Method ID'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
                'regexp'        => '^([0-9]{1,10})$',
            ],
            [
                'name'          => 'preapprovedfrequency',
                'external_name' => 'PreapprovedFrequency',
                'display_name'  => self::s2p_t('Preapproval frequency'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
                'value_source'  => S2P_SDK_Values_Source::TYPE_PREAPPROVAL_FREQUENCY,
            ],
            [
                'name'          => 'mandatereference',
                'external_name' => 'MandateReference',
                'display_name'  => self::s2p_t('Preapproval mandate reference'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
        ];
    }
}
