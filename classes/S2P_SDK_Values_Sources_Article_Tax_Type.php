<?php

namespace S2P_SDK;

class S2P_SDK_Values_Sources_Article_Tax_Type extends S2P_SDK_Language
{
    const TYPE_WITHOUT_VAT = 1, TYPE_VAT_ZERO = 2, TYPE_VAT_TEN = 3, TYPE_VAT_EIGHTEEN = 4, TYPE_VAT_TEN_TEN_ZERO = 5, TYPE_VAT_HUNDRED_EIGHTEEN = 6;

    private static $TYPES_ARR = array(
        self::TYPE_WITHOUT_VAT => 'Without VAT',
        self::TYPE_VAT_ZERO => '0% VAT rate',
        self::TYPE_VAT_TEN => '10% VAT rate',
        self::TYPE_VAT_EIGHTEEN => '18% VAT rate',
        self::TYPE_VAT_TEN_TEN_ZERO => '10/110 VAT rate',
        self::TYPE_VAT_HUNDRED_EIGHTEEN => '18/118 VAT rate',
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
