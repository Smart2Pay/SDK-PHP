<?php

namespace S2P_SDK;

class S2P_SDK_Values_Sources_Article_Type extends S2P_SDK_Language
{
    const TYPE_PRODUCT = 1, TYPE_SHIPPING = 2, TYPE_HANDLING = 3, TYPE_DISCOUNT = 4,
        TYPE_PHYSICAL = 5, TYPE_SHIPPING_FEE = 6, TYPE_SALES_TAX = 7, TYPE_DIGITAL = 8, TYPE_GIFT_CARD = 9, TYPE_STORE_CREDIT = 10, TYPE_SURCHARGE = 11;

    private static $TYPES_ARR = array(
        self::TYPE_PRODUCT => 'Product',
        self::TYPE_SHIPPING => 'Shipping',
        self::TYPE_HANDLING => 'Handling',

        self::TYPE_DISCOUNT => 'Discount',

        self::TYPE_PHYSICAL => 'Physical',
        self::TYPE_SHIPPING_FEE => 'Shipping Fee',
        self::TYPE_SALES_TAX => 'Salex Tax',
        self::TYPE_DIGITAL => 'Digital',
        self::TYPE_GIFT_CARD => 'Gift card',
        self::TYPE_STORE_CREDIT => 'Store credit',
        self::TYPE_SURCHARGE => 'Surcharge',
    );

    public static function get_types()
    {
        return self::$TYPES_ARR;
    }

    public static function valid_type( $type )
    {
        if( empty( $type )
         or !($types_arr = self::get_types()) or empty( $types_arr[$type] ) )
            return false;

        return $types_arr[$type];
    }

    public static function guess_from_term( $term )
    {
        $all_terms_arr = self::get_types();

        $found_terms = array();
        foreach( $all_terms_arr as $key => $val )
        {
            if( stristr( $term, $val ) === false )
                continue;

            $found_terms[$key] = $val;
        }

        return $found_terms;
    }
}
