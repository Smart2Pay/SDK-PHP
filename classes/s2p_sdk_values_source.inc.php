<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_CLASSES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_currencies.inc.php' );
include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_countries.inc.php' );

class S2P_SDK_Values_Source extends S2P_SDK_Language
{
    const ERR_TYPE = 1;

    const TYPE_COUNTRY = 1, TYPE_CURRENCY = 2;

    private static $TYPES_ARR = array(
        self::TYPE_COUNTRY => 'Country',
        self::TYPE_CURRENCY => 'Currency',
    );

    /** @var int $_type */
    private $_type = 0;

    function __construct( $type = 0 )
    {
        parent::__construct();

        $this->_type = 0;

        $type = intval( $type );
        if( !empty( $type ) )
            $this->source_type( $type );
    }

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

    public function source_type( $type = null )
    {
        if( $type === null )
            return $this->_type;

        $type = intval( $type );
        if( !self::valid_type( $type ) )
            return false;

        $this->_type = $type;
        return $this->_type;
    }

    public function get_option_values()
    {
        $this->reset_error();

        switch( $this->_type )
        {
            default:
                $this->set_error( self::ERR_TYPE, self::s2p_t( 'Unknown source type.' ) );
                return false;
            break;

            case self::TYPE_COUNTRY:
                $return_arr = S2P_SDK_Countries::get_countries();
                foreach( $return_arr as $key => $text )
                {
                    $return_arr[$key] = $key.' - '.$text;
                }
            break;

            case self::TYPE_CURRENCY:
                $return_arr = S2P_SDK_Currencies::get_currencies();
                foreach( $return_arr as $key => $text )
                {
                    $return_arr[$key] = $key.' - '.$text;
                }
            break;
        }

        return $return_arr;
    }

    public function valid_value( $val )
    {
        $this->reset_error();

        switch( $this->_type )
        {
            default:
                $this->set_error( self::ERR_TYPE, self::s2p_t( 'Unknown source type.' ) );
                return false;
            break;

            case self::TYPE_COUNTRY:
                $return_check = S2P_SDK_Countries::valid_country( $val );
            break;

            case self::TYPE_CURRENCY:
                $return_check = S2P_SDK_Currencies::valid_currency( $val );
            break;
        }

        return $return_check;
    }
}
