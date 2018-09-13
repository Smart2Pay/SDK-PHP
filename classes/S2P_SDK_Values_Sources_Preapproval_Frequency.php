<?php

namespace S2P_SDK;

class S2P_SDK_Values_Sources_Preapproval_Frequency extends S2P_SDK_Language
{
    const FREQ_ONETIME = 'onetime', FREQ_DAILY = 'daily', FREQ_WEEKLY = 'weekly', FREQ_MONTHLY = 'monthly';

    private static $FREQ_ARR = array(
        self::FREQ_ONETIME => 'One time',
        self::FREQ_DAILY => 'Daily',
        self::FREQ_WEEKLY => 'Weekly',
        self::FREQ_MONTHLY => 'Monthly',
    );

    public static function get_frequencies()
    {
        return self::$FREQ_ARR;
    }

    public static function valid_frequency( $freq )
    {
        if( empty( $freq )
         or !($freqs_arr = self::get_frequencies()) or empty( $freqs_arr[$freq] ) )
            return false;

        return $freqs_arr[$freq];
    }

    public static function guess_from_term( $term )
    {
        $all_terms_arr = self::get_frequencies();

        $found_terms = array();
        foreach( $all_terms_arr as $key => $val )
        {
            if( stristr( $term, $val ) === false
            and stristr( $term, $key ) === false )
                continue;

            $found_terms[$key] = $val;
        }

        return $found_terms;
    }
}
