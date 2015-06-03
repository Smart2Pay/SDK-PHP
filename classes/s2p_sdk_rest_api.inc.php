<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_CLASSES' ) )
    die( 'Something went bad' );

include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_rest_api_request.inc.php' );
include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_rest_api_response.inc.php' );

if( !defined( 'S2P_SDK_TEST' ) )
    define( 'S2P_SDK_TEST', 'test' );
if( !defined( 'S2P_SDK_LIVE' ) )
    define( 'S2P_SDK_LIVE', 'live' );

class S2P_SDK_Rest_API extends S2P_SDK_Module
{
    const VERSION = '1.0';

    const ERR_ENVIRONMENT = 200, ERR_METHOD = 201, ERR_METHOD_FUNC = 202, ERR_PREPARE_REQUEST = 203, ERR_URL = 204, ERR_APIKEY = 205, ERR_CURL_CALL = 206;

    const TEST_BASE_URL = 'https://paytest.smart2pay.com',
          LIVE_BASE_URL = 'https://pay.smart2pay.com';

    /** @var bool $_test_mode */
    private $_environment = S2P_SDK_LIVE;

    /** @var string $_base_url */
    private $_base_url = '';

    /** @var S2P_SDK_Method $_method */
    private $_method = null;

    /** @var S2P_SDK_Rest_API_Request $_request */
    private $_request = null;

    private $_api_key = '';

    private $_request_result = null;

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed If this function returns false it will consider module is not initialized correctly and will not return class instance
     */
    public function init( $module_params = false )
    {
        $this->reset_api();

        if( empty( $module_params ) or ! is_array( $module_params ) )
            $module_params = array();

        if( ! empty( $module_params['method'] ) )
        {
            if( !$this->method( $module_params['method'], $module_params ) )
                return false;
        }

        if( !empty( $module_params['environment'] ) )
        {
            if( !$this->environment( $module_params['environment'] ) )
                return false;
        }

        if( !empty( $module_params['api_key'] ) )
        {
            if( !$this->set_api_key( $module_params['api_key'] ) )
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
        $this->reset_api();
    }

    function __construct( $params = false )
    {
        parent::__construct();
        $this->init( $params );
    }

    private function reset_api()
    {
        $this->_method = null;
        $this->_request = null;
        $this->_request_result = null;
        $this->_base_url = '';
        $this->_api_key = '';
    }

    public function environment( $env = null )
    {
        if( $env === null )
            return $this->_environment;

        if( !in_array( $env, array( S2P_SDK_TEST, S2P_SDK_LIVE ) ) )
        {
            $this->set_error( self::ERR_ENVIRONMENT,
                                  self::s2p_t( 'Unknown environment' ),
                                  sprintf( 'Unknown environment [%s]', $env ) );
            return false;
        }

        $this->_environment = $env;
        return true;
    }

    /**
     * Get current base URL
     *
     * @return string Returns base URL
     */
    public function get_base_url()
    {
        return $this->_base_url;
    }

    /**
     * Set base URL
     *
     * @param string $url
     *
     * @return bool Returns true on success or false on fail
     */
    public function set_base_url( $url )
    {
        if( !is_string( $url ) )
            return false;

        $this->_base_url = $url;
        return true;
    }

    /**
     * Get api key
     *
     * @return string Returns api key
     */
    public function get_api_key()
    {
        return $this->_api_key;
    }

    /**
     * Set base URL
     *
     * @param string $url
     *
     * @return bool Returns true on success or false on fail
     */
    public function set_api_key( $api_key )
    {
        if( !is_string( $api_key ) )
            return false;

        $this->_api_key = $api_key;
        return true;
    }

    private function validate_base_url()
    {
        $env = $this->environment();

        switch( $env )
        {
            default:
                $this->set_error( self::ERR_ENVIRONMENT,
                    self::s2p_t( 'Unknown environment' ),
                    sprintf( 'Unknown environment [%s]', $env ) );

                return false;

            case S2P_SDK_TEST:
                if( empty( $this->_base_url ) )
                    $this->_base_url = self::TEST_BASE_URL;
            break;

            case S2P_SDK_LIVE:
                if( empty( $this->_base_url ) )
                    $this->_base_url = self::LIVE_BASE_URL;
            break;
        }

        return true;
    }

    public function do_call( $params = false )
    {
        if( empty( $this->_method ) )
        {
            $this->set_error( self::ERR_METHOD, self::s2p_t( 'Method not set' ) );
            return false;
        }

        if( !($api_key = $this->get_api_key()) )
        {
            $this->set_error( self::ERR_APIKEY, self::s2p_t( 'API Key not set.' ) );
            return false;
        }

        if( !$this->validate_base_url()
         or empty( $this->_base_url ) )
        {
            $this->set_error( self::ERR_URL, self::s2p_t( 'Couldn\'t obtain base URL.' ) );
            return false;
        }

        if( !($request_data = $this->_method->prepare_for_request( $params )) )
        {
            if( $this->_method->has_error() )
                $this->copy_error( $this->_method );
            else
                $this->set_error( self::ERR_PREPARE_REQUEST, self::s2p_t( 'Couldn\'t prepare request data.' ) );

            return false;
        }

        $final_url = $this->_base_url.$request_data['full_query'];

        $this->_request = new S2P_SDK_Rest_API_Request();

        $this->_request->set_url( $final_url );

        if( !empty( $request_data['request_body'] ) )
            $this->_request->set_body( $request_data['request_body'] );

        // $this->_request->add_header( 'Authorization', 'Basic '.@base64_encode( $api_key ) );

        $call_params = array();
        $call_params['user_agent'] = 'APISDK_'.$this->get_version().'/PHP_'.phpversion().'/'.php_uname('s').'_'.php_uname('r');
        $call_params['userpass'] = array( 'user' => $api_key, 'pass' => '' );

        if( !($this->_request_result = $this->_request->do_curl( $call_params )) )
            $this->_request_result = null;

        if( !empty( $this->_request_result ) and $this->_request_result['http_code'] != 200 )
        {
            var_dump( $this->_request_result );

            $this->set_error( self::ERR_CURL_CALL, self::s2p_t( 'Request responded with error code %s', $this->_request_result['http_code'] ) );
            return false;
        }

        return true;
    }

    public function method( $method, $params = null )
    {
        $this->_method = null;

        $method = 'S2P_SDK_Meth_'.ucfirst( strtolower( $method ) );

        /** @var S2P_SDK_Method $method */
        if( !($method_obj = self::get_instance( $method )) )
        {
            if( self::st_has_error() )
                $this->copy_static_error();
            else
                $this->set_error( self::ERR_METHOD, self::s2p_t( 'Error instantiating method.' ) );

            return false;
        }

        $this->_method = $method_obj;

        if( !empty( $params ) and is_array( $params ) )
        {
            $this->method_request_parameters( $params );

            if( !empty( $params['func'] ) )
            {
                if( !$this->method_functionality( $params['func'] ) )
                {
                    $this->reset_api();
                    return false;
                }
            }
        }

        return true;
    }

    public function method_request_parameters( $params )
    {
        if( empty( $params ) or !is_array( $params ) )
            return true;

        if( empty( $this->_method ) )
        {
            $this->set_error( self::ERR_METHOD, self::s2p_t( 'Method not set' ) );
            return false;
        }

        $this->_method->request_parameters( $params );

        return true;
    }

    public function method_functionality( $func )
    {
        if( empty( $this->_method ) )
        {
            $this->set_error( self::ERR_METHOD, self::s2p_t( 'Method not set' ) );
            return false;
        }

        if( !$this->_method->method_functionality( $func ) )
        {
            if( $this->_method->has_error() )
                $this->copy_error( $this->_method );
            else
                $this->set_error( self::ERR_METHOD_FUNC,
                                    self::s2p_t( 'Invalid method functionality' ),
                                    sprintf( 'Invalid method functionality [%s]', $func ) );

            return false;
        }

        return true;
    }

    public static function get_version()
    {
        return self::VERSION;
    }

}
