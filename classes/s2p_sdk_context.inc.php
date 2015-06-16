<?php

namespace S2P_SDK;

/**
 * Contains any values which create dependencies
 *
 * Class S2P_SDK_Context
 * @package S2P_SDK
 */
class S2P_SDK_Context extends S2P_SDK_Language
{
    /** @var string $_country */
    private static $_country = '';

    /** @var int $_methodid */
    private static $_methodid = 0;

    /** @var string $_currency */
    private static $_currency = '';

    function __construct()
    {
        parent::__construct();

        $this->reset_context();
    }

    public function reset_context()
    {
        $this->_country = '';
        $this->_methodid = 0;
        $this->_currency = '';
    }

    public static function valid_country( $country )
    {
        if( empty( $country )
         or !($countries_arr = self::get_countries()) or empty( $countries_arr[$country] ) )
            return false;

        return $countries_arr[$country];
    }

    public static function guess_from_term( $term )
    {
        $all_terms_arr = self::get_countries();

        $found_terms = array();
        foreach( $all_terms_arr as $key => $val )
        {
            if( stristr( $term, $key ) === false
             or stristr( $term, $val ) === false )
                continue;

            $found_terms[$key] = $val;
        }

        return $found_terms;
    }
}
