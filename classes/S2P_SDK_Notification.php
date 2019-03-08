<?php

namespace S2P_SDK;

class S2P_SDK_Notification extends S2P_SDK_Module
{
    const ERR_UNKNOWN_TYPE = 1, ERR_BODY = 2, ERR_JSON = 3, ERR_RESPONSE_OK = 4, ERR_AUTHENTICATION = 5;

    const TYPE_PAYMENT = 1, TYPE_PREAPPROVAL = 2, TYPE_REFUND = 3, TYPE_DISPUTE = 4;
    private static $TYPES_ARR = array(
        self::TYPE_PAYMENT => array(
            'title' => 'Payment',
        ),
        self::TYPE_PREAPPROVAL => array(
            'title' => 'Preapproval',
        ),
        self::TYPE_REFUND => array(
            'title' => 'Refund',
        ),
        self::TYPE_DISPUTE => array(
            'title' => 'Dispute',
        ),
    );

    /** @var string $_notification_buffer */
    private $_notification_buffer = '';

    /** @var S2P_SDK_Scope_Structure $_notification_object */
    private $_notification_object = null;

    /** @var array $_notification_array */
    private $_notification_array = array();

    /** @var int $_type */
    private $_type = 0;

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

    public static function get_type_title( $type )
    {
        if( !($type_arr = self::valid_type( $type )) )
            return false;

        return $type_arr['title'];
    }

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed If this function returns false it will consider module is not initialized correctly and will not return class instance
     */
    public function init( $module_params = false )
    {
        $this->reset_notification();

        if( empty( $module_params ) or ! is_array( $module_params ) )
            $module_params = array();

        if( !isset( $module_params['auto_extract_parameters'] ) )
            $module_params['auto_extract_parameters'] = true;

        if( !empty( $module_params['auto_extract_parameters'] ) )
        {
            if( !$this->extract_parameters() )
                return false;
        }

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
        $this->reset_notification();
    }

    private function reset_notification()
    {
        $this->_type = 0;
        $this->_notification_buffer = '';
        $this->_notification_object = null;
        $this->_notification_array = array();
    }

    public function get_input_buffer()
    {
        if( empty( $this->_notification_buffer ) )
            $this->extract_parameters();

        return $this->_notification_buffer;
    }

    public function get_type()
    {
        if( empty( $this->_type ) )
            $this->extract_parameters();

        return $this->_type;
    }

    public function get_object()
    {
        if( empty( $this->_notification_object ) )
            $this->extract_parameters();

        return $this->_notification_object;
    }

    public function get_array()
    {
        if( empty( $this->_notification_array ) )
            $this->extract_parameters();

        return $this->_notification_array;
    }

    public function check_authentication()
    {
        if( empty( $_SERVER['PHP_AUTH_USER'] ) or empty( $_SERVER['PHP_AUTH_PW'] ) )
        {
            if( empty( $_SERVER['HTTP_AUTHORIZATION'] ) and empty( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) )
            {
                $this->set_error( self::ERR_AUTHENTICATION, self::s2p_t( 'No authentication.' ) );
                return false;
            }

            if( !empty( $_SERVER['HTTP_AUTHORIZATION'] )
            and ($auth_arr = explode(':', @base64_decode( trim( substr( $_SERVER['HTTP_AUTHORIZATION'], 6 ) ) ) ))
            and count( $auth_arr ) == 2 )
            {
                $_SERVER['PHP_AUTH_USER'] = $auth_arr[0];
                $_SERVER['PHP_AUTH_PW'] = $auth_arr[1];
            }

            if( empty( $_SERVER['PHP_AUTH_USER'] ) and empty( $_SERVER['PHP_AUTH_PW'] )
            and !empty( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] )
            and ($auth_arr = explode(':', @base64_decode( trim( substr( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6 ) ) ) ))
            and count( $auth_arr ) == 2 )
            {
                $_SERVER['PHP_AUTH_USER'] = $auth_arr[0];
                $_SERVER['PHP_AUTH_PW'] = $auth_arr[1];
            }

            if( empty( $_SERVER['PHP_AUTH_USER'] ) or empty( $_SERVER['PHP_AUTH_PW'] ) )
            {
                $this->set_error( self::ERR_AUTHENTICATION, self::s2p_t( 'No authentication.' ) );
                return false;
            }
        }

        $api_config_arr = self::get_api_configuration();

        if( empty( $api_config_arr['api_key'] ) or empty( $api_config_arr['site_id'] ) )
        {
            $this->set_error( self::ERR_AUTHENTICATION, self::s2p_t( 'API Key not set in config file.' ) );
            return false;
        }

        if( $_SERVER['PHP_AUTH_USER'] != $api_config_arr['site_id']
         or $_SERVER['PHP_AUTH_PW'] != $api_config_arr['api_key'] )
        {
            $this->set_error( self::ERR_AUTHENTICATION, self::s2p_t( 'Request doesn\'t match API Key or Site ID configuration.' ) );
            return false;
        }

        return true;
    }

    public function extract_parameters()
    {
        static $extracted = null;

        if( $extracted !== null )
            return true;

        $extracted = true;

        if( !($this->_notification_buffer = S2P_SDK_Helper::get_php_input()) )
        {
            $this->set_error( self::ERR_BODY, self::s2p_t( 'Notification body is empty.' ) );
            return false;
        }

        if( !($notification_arr = @json_decode( $this->_notification_buffer, true )) )
        {
            $this->set_error( self::ERR_JSON, self::s2p_t( 'Notification body is not a JSON.' ) );
            return false;
        }

        if( ($methods_arr = S2P_SDK_Method::get_all_methods())
        and is_array( $methods_arr ) )
        {
            foreach( $methods_arr as $method_name => $method_arr )
            {
                /** @var S2P_SDK_Method $instance */
                if( !($instance = $method_arr['instance'])
                 or !($notification_check = $instance->check_notification( $notification_arr )) )
                    continue;

                if( !empty( $notification_check['notification_array'] ) )
                    $this->_notification_array = $notification_check['notification_array'];

                switch( $notification_check['notification_type'] )
                {
                    case 'Payment':
                        $this->_type = self::TYPE_PAYMENT;
                    break;
                    case 'Preapproval':
                        $this->_type = self::TYPE_PREAPPROVAL;
                    break;
                    case 'Refund':
                        $this->_type = self::TYPE_REFUND;
                    break;
                    case 'Dispute':
                        $this->_type = self::TYPE_DISPUTE;
                    break;
                }

                return true;
            }
        }

        return true;
    }

    public function respond_ok()
    {
        $this->reset_error();

        if( @headers_sent( $file, $line ) )
        {
            $this->set_error( self::ERR_RESPONSE_OK, self::s2p_t( 'Headers already set from file %s, line %s.', $file, $line ) );
            return false;
        }

        @header( 'HTTP/1.1 204 No Content' );

        return true;
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

        if( !($log_size = @filesize( S2P_SDK_DIR_PATH.'log_demo.log' )) )
            $log_size = 0;

        if( !($fil = @fopen( S2P_SDK_DIR_PATH.'log_demo.log', 'a' )) )
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
        if( defined( 'S2P_SDK_NOTIFICATION_IDENTIFIER' ) and constant( 'S2P_SDK_NOTIFICATION_IDENTIFIER' ) )
            $notification_identifier = constant( 'S2P_SDK_NOTIFICATION_IDENTIFIER' );

        @fputs( $fil, date( 'd-m-Y H:i:s' ).' | '.
                      (!empty( $notification_identifier )?str_pad( $notification_identifier, 15, ' ', STR_PAD_LEFT ).' | ':'').
                      str_pad( $request_ip, 15, ' ', STR_PAD_LEFT ).' | '.
                      $str."\n" );

        @fflush( $fil );
        @fclose( $fil );

        return true;
    }
}
