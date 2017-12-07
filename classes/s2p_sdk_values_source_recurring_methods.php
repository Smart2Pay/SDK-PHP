<?php

namespace S2P_SDK;

class S2P_SDK_Values_Source_Recurring_Methods extends S2P_SDK_Language
{
    const METH_MERCADOPAGO = 46, METH_PAYWITHMYBANK = 58, METH_CARDS = 69, METH_KLARNAINVOICE = 75, METH_QIWIWALLET = 1003;

    private static $METHODS_ARR = array(
        self::METH_MERCADOPAGO => array(
            'title' => 'MercadoPago',
        ),
        self::METH_PAYWITHMYBANK => array(
            'title' => 'PayWithMyBank',
        ),
        self::METH_CARDS => array(
            'title' => 'Cards',
        ),
        self::METH_KLARNAINVOICE => array(
            'title' => 'Klarna Invoice',
        ),
        self::METH_QIWIWALLET => array(
            'title' => 'QIWI Wallet',
        ),
    );

    public static function get_methods()
    {
        return self::$METHODS_ARR;
    }

    public static function valid_method_id( $method_id )
    {
        if( empty( $method_id )
         or !($methods_arr = self::get_methods()) or empty( $methods_arr[$method_id] ) )
            return false;

        return $methods_arr[$method_id];
    }

    public static function guess_from_term( $term )
    {
        $all_terms_arr = self::get_methods();

        $found_terms = array();
        foreach( $all_terms_arr as $key => $val )
        {
            if( stristr( $term, $val['title'] ) === false )
                continue;

            $found_terms[$key] = $val['title'];
        }

        return $found_terms;
    }
}
