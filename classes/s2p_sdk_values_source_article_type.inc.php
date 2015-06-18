<?php

namespace S2P_SDK;

class S2P_SDK_Values_Sources_Article_Type extends S2P_SDK_Language
{
    private static $TYPES_ARR = array(
        1 => 'Product',
        2 => 'Shipping',
        3 => 'Handling',
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
