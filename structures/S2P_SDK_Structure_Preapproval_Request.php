<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Preapproval_Request extends S2P_SDK_Scope_Structure
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

        return [
            [
                'name'          => 'merchantpreapprovalid',
                'external_name' => 'MerchantPreapprovalID',
                'display_name'  => self::s2p_t('Preapproval merchant assigned ID'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'methodid',
                'external_name' => 'MethodID',
                'type'          => S2P_SDK_VTYPE_INT,
                'display_name'  => self::s2p_t('Preapproval method ID'),
                'regexp'        => '^\d{1,12}$',
                'default'       => 0,
                'value_source'  => S2P_SDK_Values_Source::TYPE_RECURRING_METHODS,
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
                'name'            => 'executiondate',
                'external_name'   => 'ExecutionDate',
                'display_name'    => self::s2p_t('Executed at a specific date'),
                'type'            => S2P_SDK_VTYPE_DATE,
                'default'         => '',
                'skip_if_default' => true,
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
                'name'          => 'description',
                'external_name' => 'Description',
                'display_name'  => self::s2p_t('Preapproval description'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'regexp'        => '^.{1,255}$',
                'default'       => '',
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
                'value_source'  => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ],
            [
                'name'           => 'returnurl',
                'external_name'  => 'ReturnURL',
                'display_name'   => self::s2p_t('Preapproval return URL'),
                'type'           => S2P_SDK_VTYPE_STRING,
                'default'        => '',
                'regexp'         => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
                'check_constant' => 'S2P_SDK_PAYMENT_RETURN_URL',
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
        ];
    }
}
