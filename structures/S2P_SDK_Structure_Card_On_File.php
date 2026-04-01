<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Card_On_File extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'cardonfile',
            'external_name' => 'CardOnFile',
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
                'name'          => 'isinitial',
                'external_name' => 'IsInitial',
                'display_name'  => self::s2p_t('Is initial'),
                'type'          => S2P_SDK_VTYPE_BOOL,
                'default'       => true,
            ],
            [
                'name'          => 'type',
                'external_name' => 'TransactionType',
                'display_name'  => self::s2p_t('Transaction type'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'initialpaymentid',
                'external_name' => 'InitialPaymentID',
                'display_name'  => self::s2p_t('Initial payment id'),
                'type'          => S2P_SDK_VTYPE_INT,
                'default'       => 0,
            ],
        ];
    }
}
