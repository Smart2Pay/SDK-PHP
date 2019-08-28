<?php

namespace S2P_SDK;

class S2P_SDK_Values_Source extends S2P_SDK_Language
{
    const ERR_TYPE = 1;

    const TYPE_COUNTRY = 1, TYPE_CURRENCY = 2, TYPE_AVAILABLE_METHODS = 3, TYPE_METHODS = 4, TYPE_RECURRING_METHODS = 5,
          TYPE_ARTICLE_TYPE = 6, TYPE_PREAPPROVAL_FREQUENCY = 7, TYPE_ARTICLE_TAX_TYPE = 8;

    private static $TYPES_ARR = array(
        self::TYPE_COUNTRY => 'Country',
        self::TYPE_CURRENCY => 'Currency',
        self::TYPE_AVAILABLE_METHODS => 'Available Methods',
        self::TYPE_METHODS => 'Methods',
        self::TYPE_RECURRING_METHODS => 'Recurring Methods',
        self::TYPE_ARTICLE_TYPE => 'Article Type',
        self::TYPE_PREAPPROVAL_FREQUENCY => 'Preapproval Frequency',
        self::TYPE_ARTICLE_TAX_TYPE => 'Article Tax Type',
    );

    /** @var int $_type */
    private $_type = 0;

    /** @var bool $_remote_calls */
    private $_remote_calls = false;

    function __construct( $type = 0 )
    {
        parent::__construct();

        $this->_type = 0;

        $type = (int)$type;
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

    public function remote_calls( $remote_calls = null )
    {
        if( $remote_calls === null )
            return $this->_remote_calls;

        $remote_calls = (!empty( $remote_calls )?true:false);

        $this->_remote_calls = $remote_calls;
        return $this->_remote_calls;
    }

    public function get_option_values( $params = false, $api_params = false )
    {
        $this->reset_error();

        if( empty( $api_params ) or !is_array( $api_params ) )
            $api_params = array();

        if( empty( $params ) or !is_array( $params ) )
            $params = array();
        if( empty( $params['full_details'] ) )
            $params['full_details'] = false;

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
                    $return_arr[$key] = $text.' - '.$key;
                }
            break;

            case self::TYPE_CURRENCY:
                $return_arr = S2P_SDK_Currencies::get_currencies();
                foreach( $return_arr as $key => $text )
                {
                    $return_arr[$key] = $key.' - '.$text;
                }
            break;

            case self::TYPE_AVAILABLE_METHODS:
                if( !$this->remote_calls() )
                    $return_arr = array();

                else
                {
                    $return_arr = S2P_SDK_Values_Source_Methods::get_available_methods( $api_params );
                    foreach( $return_arr as $key => $method_arr )
                    {
                        if( empty( $params['full_details'] ) )
                            $return_arr[ $key ] = $method_arr['displayname'].' ('.$key.')';
                        else
                            $return_arr[ $key ] = $method_arr;
                    }
                }
            break;

            case self::TYPE_RECURRING_METHODS:
                $return_arr = S2P_SDK_Values_Source_Recurring_Methods::get_methods();
                foreach( $return_arr as $key => $method_arr )
                {
                    $return_arr[$key] = $key.' - '.$method_arr['title'];
                }
            break;

            case self::TYPE_METHODS:
                if( !$this->remote_calls() )
                    $return_arr = array();

                else
                {
                    $return_arr = S2P_SDK_Values_Source_Methods::get_all_methods( $api_params );
                    foreach( $return_arr as $key => $method_arr )
                    {
                        if( empty( $params['full_details'] ) )
                            $return_arr[ $key ] = $method_arr['displayname'].' ('.$key.')';
                        else
                            $return_arr[ $key ] = $method_arr;
                    }
                }
            break;

            case self::TYPE_ARTICLE_TYPE:
                $return_arr = S2P_SDK_Values_Sources_Article_Type::get_types();
                foreach( $return_arr as $key => $text )
                {
                    $return_arr[$key] = $key.' - '.$text;
                }
            break;

            case self::TYPE_PREAPPROVAL_FREQUENCY:
                $return_arr = S2P_SDK_Values_Sources_Preapproval_Frequency::get_frequencies();
                foreach( $return_arr as $key => $text )
                {
                    $return_arr[$key] = $text.' ('.$key.')';
                }
            break;

            case self::TYPE_ARTICLE_TAX_TYPE:
                $return_arr = S2P_SDK_Values_Sources_Article_Tax_Type::get_types();
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

            case self::TYPE_AVAILABLE_METHODS:
                if( !$this->remote_calls() )
                    $return_check = (is_numeric( $val )?true:false);
                else
                    $return_check = S2P_SDK_Values_Source_Methods::valid_available_method_id( $val );
            break;

            case self::TYPE_METHODS:
                if( !$this->remote_calls() )
                    $return_check = (is_numeric( $val )?true:false);
                else
                    $return_check = S2P_SDK_Values_Source_Methods::valid_method_id( $val );
            break;

            case self::TYPE_RECURRING_METHODS:
                $return_check = S2P_SDK_Values_Source_Recurring_Methods::valid_method_id( $val );
            break;

            case self::TYPE_ARTICLE_TYPE:
                $return_check = S2P_SDK_Values_Sources_Article_Type::valid_type( $val );
            break;

            case self::TYPE_PREAPPROVAL_FREQUENCY:
                $return_check = S2P_SDK_Values_Sources_Preapproval_Frequency::valid_frequency( $val );
            break;

            case self::TYPE_ARTICLE_TAX_TYPE:
                $return_check = S2P_SDK_Values_Sources_Article_Tax_Type::valid_type( $val );
            break;
        }

        return $return_check;
    }
}
