<?php

namespace S2P_SDK;

class S2P_SDK_Return extends S2P_SDK_Module
{
    /** @var array $_return_params */
    private $_return_params = null;

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed If this function returns false it will consider module is not initialized correctly and will not return class instance
     */
    public function init( $module_params = false )
    {
        $this->reset_return();

        if( empty( $module_params ) or ! is_array( $module_params ) )
            $module_params = array();

        if( !isset( $module_params['auto_extract_parameters'] ) )
            $module_params['auto_extract_parameters'] = true;

        if( !empty( $module_params['auto_extract_parameters'] ) )
            $this->extract_parameters();

        return true;
    }

    /**
     * This method is called when destroy_instances() method is called inside any module.
     * This is ment to be destructor of instances.
     * Make sure you call destroy_instances() when you don't need any data held in any instances of modules
     *
     * @see destroy_instances()
     */
    public function destroy()
    {
        $this->reset_return();
    }

    private function reset_return()
    {
        $this->_return_params = array();
    }

    public function get_parameters()
    {
        if( empty( $this->_return_params ) )
            $this->extract_parameters();

        return $this->_return_params;
    }

    public static function default_extra_info()
    {
        return array(
            'has_data' => false,
            'AccountHolder' => '',
            'BankName' => '',
            'AccountNumber' => '',
            'IBAN' => '',
            'SWIFT_BIC' => '',
            'AccountCurrency' => '',

            'EntityNumber' => '',

            'ReferenceNumber' => '',
            'AmountToPay' => '',
        );
    }

    public static function default_parameters()
    {
        return array(
            'data' => 0,
            'MerchantTransactionID' => '',
            'extra_info' => self::default_extra_info(),
        );
    }

    public function extract_parameters()
    {
        $default_parameters = self::default_parameters();
        $default_transaction_info = self::default_extra_info();

        $params = $default_parameters;
        foreach( $default_parameters as $key => $def_val )
        {
            if( $key == 'extra_info' )
                continue;

            if( isset( $_GET[$key] ) )
                $params[$key] = $_GET[$key];
        }

        foreach( $default_transaction_info as $key => $def_val )
        {
            if( $key == 'has_data' )
                continue;

            if( isset( $_GET[$key] ) )
            {
                $params['extra_info']['has_data'] = true;
                $params['extra_info'][$key] = $_GET[$key];
            }
        }

        $this->_return_params = $params;

        return $params;
    }

    public static function logging_enabled( $log = null )
    {
        static $logging = false;

        if( $log === null )
            return $logging;

        $logging = (!empty( $log )?true:false);
        return $logging;
    }

    public static function logf()
    {
        if( !self::logging_enabled() )
            return true;

        if( !($args_num = func_num_args())
         or !($args_arr = func_get_args()) )
            return false;

        $str = array_shift( $args_arr );

        $also_echo = true;
        if( !empty( $args_arr ) and is_array( $args_arr )
        and ($len = count( $args_arr ))
        and is_bool( $args_arr[$len-1] ) )
        {
            $also_echo = ( !empty( $args_arr[$len - 1] ) ? true : false );
            array_pop( $args_arr );

            if( empty( $args_arr ) )
                $args_arr = array();
        }

        if( !empty( $args_arr ) )
            $str = vsprintf( $str, $args_arr );

        if( $str === '' )
            return false;

        if( !empty( $also_echo ) )
            echo $str;

        @clearstatcache();

        if( !($log_size = @filesize( S2P_SDK_DIR_PATH.'log_return.log' )) )
            $log_size = 0;

        if( !($fil = @fopen( S2P_SDK_DIR_PATH.'log_return.log', 'a' )) )
            return false;

        if( empty( $log_size ) )
        {
            fputs( $fil, "        Date        |    Identifier   |      IP         |  Log\n" );
            fputs( $fil, "--------------------+-----------------+-----------------+---------------------------------------------------\n" );
        }

        if( !empty( $_SERVER['REMOTE_ADDR'] ) )
            $request_ip = $_SERVER['REMOTE_ADDR'];
        else
            $request_ip = '(unknown)';

        $notification_identifier = '';
        if( defined( 'S2P_SDK_RETURN_IDENTIFIER' ) and constant( 'S2P_SDK_RETURN_IDENTIFIER' ) )
            $notification_identifier = constant( 'S2P_SDK_RETURN_IDENTIFIER' );

        @fputs( $fil, date( 'd-m-Y H:i:s' ).' | '.
                      (!empty( $notification_identifier )?str_pad( $notification_identifier, 15, ' ', STR_PAD_LEFT ).' | ':'').
                      str_pad( $request_ip, 15, ' ', STR_PAD_LEFT ).' | '.
                      $str."\n" );

        @fflush( $fil );
        @fclose( $fil );

        return true;
    }
}
