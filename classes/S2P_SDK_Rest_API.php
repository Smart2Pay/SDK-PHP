<?php

namespace S2P_SDK;


if( !defined( 'S2P_SDK_REST_TEST' ) )
    define( 'S2P_SDK_REST_TEST', 'test' );
if( !defined( 'S2P_SDK_REST_LIVE' ) )
    define( 'S2P_SDK_REST_LIVE', 'live' );
if( !defined( 'S2P_SDK_REST_CUSTOM' ) )
    define( 'S2P_SDK_REST_CUSTOM', 'custom' );

class S2P_SDK_Rest_API extends S2P_SDK_Module
{
    const ENV_TEST = S2P_SDK_REST_TEST, ENV_LIVE = S2P_SDK_REST_LIVE, ENV_CUSTOM = S2P_SDK_REST_CUSTOM;

    const ENTRY_POINT_REST = 1, ENTRY_POINT_CARDS = 2;

    const ERR_ENVIRONMENT = 100, ERR_METHOD = 101, ERR_METHOD_FUNC = 102, ERR_PREPARE_REQUEST = 103, ERR_URL = 104, ERR_HTTP_METHOD = 105,
          ERR_APIKEY = 106, ERR_CURL_CALL = 107, ERR_PARSE_RESPONSE = 108, ERR_VALIDATE_RESPONSE = 109, ERR_CALL_RESULT = 110, ERR_SITE_ID = 111,
          ERR_ENTRY_POINT = 112;

    const TEST_CARDS_URL = 'https://securetest.smart2pay.com',
          LIVE_CARDS_URL = 'https://secure.smart2pay.com';

    const TEST_BASE_URL = 'https://paytest.smart2pay.com',
          LIVE_BASE_URL = 'https://globalpay.smart2pay.com';

    const TEST_RESOURCE_URL = 'https://apitest.smart2pay.com',
          LIVE_RESOURCE_URL = 'https://api.smart2pay.com';

    /** @var int $_entry_point */
    private $_entry_point = self::ENTRY_POINT_REST;

    /** @var string $_environment */
    private $_environment = self::ENV_LIVE;

    /** @var string $_base_url */
    private $_base_url = '';

    /** @var string $_resource_url */
    private $_resource_url = '';

    /** @var S2P_SDK_Method $_method */
    private $_method = null;

    /** @var S2P_SDK_Rest_API_Request $_request */
    private $_request = null;

    /** @var int $_site_id */
    private $_site_id = 0;

    /** @var string $_api_key */
    private $_api_key = '';

    /** @var array $_call_result */
    private $_call_result = null;

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

        if( !empty( $module_params['method'] ) )
        {
            if( !$this->method( $module_params['method'], $module_params ) )
                return false;
        }

        $api_config_arr = self::get_api_configuration();

        if( empty( $module_params['site_id'] ) and !empty( $api_config_arr['site_id'] ) )
            $module_params['site_id'] = $api_config_arr['site_id'];
        if( empty( $module_params['api_key'] ) and !empty( $api_config_arr['api_key'] ) )
            $module_params['api_key'] = $api_config_arr['api_key'];
        if( empty( $module_params['environment'] ) and !empty( $api_config_arr['environment'] ) )
            $module_params['environment'] = $api_config_arr['environment'];
        if( empty( $module_params['custom_base_url'] ) and !empty( $api_config_arr['custom_base_url'] ) )
            $module_params['custom_base_url'] = $api_config_arr['custom_base_url'];

        if( !empty( $module_params['environment'] ) )
        {
            if( !$this->environment( $module_params['environment'] ) )
                return false;
        }

        if( !empty( $module_params['site_id'] ) )
        {
            if( !$this->set_site_id( $module_params['site_id'] ) )
                return false;
        }

        if( !empty( $module_params['api_key'] ) )
        {
            if( !$this->set_api_key( $module_params['api_key'] ) )
                return false;
        }

        if( $this->environment() == self::ENV_CUSTOM
        and !empty( $module_params['custom_base_url'] ) )
        {
            if( !$this->set_base_url( $module_params['custom_base_url'] ) )
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
        parent::__construct( $params );
    }

    private function reset_api()
    {
        $this->_entry_point = self::ENTRY_POINT_REST;
        $this->_method = null;
        $this->_request = null;
        $this->_base_url = '';
        $this->_resource_url = '';
        $this->_api_key = '';
        $this->_site_id = 0;
        $this->reset_call_result();
    }

    private function reset_call_result()
    {
        $this->_call_result = null;
    }

    public static function default_call_result()
    {
        return array(
            'final_url' => '',
            'request' => array(),
            'response' => array(),
        );
    }

    public static function validate_call_result( $result )
    {
        $default_result = self::default_call_result();
        if( empty( $result ) or !is_array( $result ) )
            return $default_result;

        $new_result = array();
        foreach( $default_result as $key => $def_val )
        {
            if( !array_key_exists( $key, $result ) )
                $new_result[$key] = $def_val;
            else
                $new_result[$key] = $result[$key];
        }

        $new_result['request'] = S2P_SDK_Rest_API_Request::validate_request_array( $new_result['request'] );
        $new_result['response'] = S2P_SDK_Method::validate_response_data( $new_result['response'] );

        return $new_result;
    }

    public static function get_resources_base_url( $module_params = false )
    {
        if( empty( $module_params ) or ! is_array( $module_params ) )
            $module_params = array();

        $api_config_arr = self::get_api_configuration();

        if( empty( $module_params['environment'] ) and !empty( $api_config_arr['environment'] ) )
            $module_params['environment'] = $api_config_arr['environment'];

        switch( $module_params['environment'] )
        {
            default:
                return '';

            case self::ENV_TEST:
            case self::ENV_CUSTOM:
                return self::TEST_RESOURCE_URL;

            case self::ENV_LIVE:
                return self::LIVE_RESOURCE_URL;
        }
    }

    public function entry_point( $ent = null )
    {
        if( $ent === null )
            return $this->_entry_point;

        if( !in_array( $ent, array( self::ENTRY_POINT_REST, self::ENTRY_POINT_CARDS ) ) )
        {
            $this->set_error( self::ERR_ENTRY_POINT,
                                  self::s2p_t( 'Invalid entry point.' ),
                                  sprintf( 'Invalid entry point. [%s]', $ent ) );
            return false;
        }

        $this->_entry_point = $ent;
        return true;
    }

    public function environment( $env = null )
    {
        if( $env === null )
            return $this->_environment;

        if( !in_array( $env, array( self::ENV_TEST, self::ENV_LIVE, self::ENV_CUSTOM ) ) )
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
     * Return last call result
     *
     * @return array Returns last call result or null in case no call was made
     */
    public function get_call_result()
    {
        return $this->_call_result;
    }

    /**
     * Return current request object
     *
     * @return S2P_SDK_Rest_API_Request Returns request object
     */
    public function get_request_obj()
    {
        return $this->_request;
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
     * Get current resource URL
     *
     * @return string Returns resource URL
     */
    public function get_resource_url()
    {
        return $this->_resource_url;
    }

    /**
     * Set resource URL
     *
     * @param string $url
     *
     * @return bool Returns true on success or false on fail
     */
    public function set_resource_url( $url )
    {
        if( !is_string( $url ) )
            return false;

        $this->_resource_url = $url;
        return true;
    }

    /**
     * Get Site ID
     *
     * @return int Returns Site ID
     */
    public function get_site_id()
    {
        return $this->_site_id;
    }

    /**
     * Set Site ID
     *
     * @param int $site_id
     *
     * @return bool Returns true on success or false on fail
     */
    public function set_site_id( $site_id )
    {
        if( !is_scalar( $site_id ) )
            return false;

        $this->_site_id = intval( $site_id );
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
     * Set API Key
     *
     * @param string $api_key
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
        $entry_point = $this->entry_point();

        switch( $env )
        {
            default:
                $this->set_error( self::ERR_ENVIRONMENT,
                    self::s2p_t( 'Unknown environment' ),
                    sprintf( 'Unknown environment [%s]', $env ) );

                return false;

            case self::ENV_CUSTOM:
                if( empty( $this->_base_url ) )
                {
                    $this->set_error( self::ERR_ENVIRONMENT,
                        self::s2p_t( 'REST API base URL not provided.' ),
                        'REST API base URL not provided.' );

                    return false;
                }

                $this->_resource_url = self::TEST_RESOURCE_URL;
            break;

            case self::ENV_TEST:
                if( $entry_point == self::ENTRY_POINT_REST )
                {
                    $this->_base_url = self::TEST_BASE_URL;
                } elseif( $entry_point == self::ENTRY_POINT_CARDS )
                {
                    $this->_base_url = self::TEST_CARDS_URL;
                }

                $this->_resource_url = self::TEST_RESOURCE_URL;
            break;

            case self::ENV_LIVE:
                if( $entry_point == self::ENTRY_POINT_REST )
                {
                    $this->_base_url = self::LIVE_BASE_URL;
                } elseif( $entry_point == self::ENTRY_POINT_CARDS )
                {
                    $this->_base_url = self::LIVE_CARDS_URL;
                }

                $this->_resource_url = self::LIVE_RESOURCE_URL;
            break;
        }

        return true;
    }

    public function do_finalize( $params = false )
    {
        if( empty( $this->_method ) )
        {
            $this->set_error( self::ERR_METHOD, self::s2p_t( 'Method not set' ) );
            return false;
        }

        if( !($call_result = $this->get_call_result()) )
        {
            $this->set_error( self::ERR_CALL_RESULT, self::s2p_t( 'Invalid call result or previous call failed.' ) );
            return false;
        }

        if( !($finalize_result = $this->_method->finalize( $call_result, $params )) )
        {
            if( $this->_method->has_error() )
                $this->copy_error( $this->_method );
            else
                $this->set_error( self::ERR_VALIDATE_RESPONSE, self::s2p_t( 'Couldn\'t finalize request after call.' ) );

            return false;
        }

        return S2P_SDK_Method::validate_finalize_result( $finalize_result );
    }

    public function do_call( $params = false )
    {
        $this->reset_call_result();
        $this->reset_error();

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['user_agent'] ) or !is_string( $params['user_agent'] ) )
            $params['user_agent'] = '';
        if( empty( $params['allow_remote_calls'] ) )
            $params['allow_remote_calls'] = false;
        if( empty( $params['quick_return_request'] ) )
            $params['quick_return_request'] = false;
        if( empty( $params['custom_validators'] ) or !is_array( $params['custom_validators'] ) )
            $params['custom_validators'] = array();
        if( empty( $params['curl_params'] ) or !is_array( $params['curl_params'] ) )
            $params['curl_params'] = array();

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

        if( !($site_id = $this->get_site_id()) )
        {
            $this->set_error( self::ERR_SITE_ID, self::s2p_t( 'Site ID not set.' ) );
            return false;
        }

        if( !$this->entry_point( $this->_method->get_entry_point() ) )
        {
            $this->set_error( self::ERR_URL, self::s2p_t( 'Invalid entry point defined in method.' ) );
            return false;
        }

        if( !$this->validate_base_url()
         or empty( $this->_base_url ) )
        {
            if( !$this->has_error() )
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

        if( ($hook_result = self::trigger_hooks( 'rest_api_prepare_request_after', array( 'api_obj' => $this, 'request_data' => $request_data ) ))
        and is_array( $hook_result ) )
        {
            if( array_key_exists( 'request_data', $hook_result ) )
                $request_data = $hook_result['request_data'];
        }

        $final_url = $this->_base_url.$request_data['full_query'];

        if( !empty( $params['quick_return_request'] ) )
        {
            $return_arr = array();
            $return_arr['final_url'] = $final_url;
            $return_arr['request_data'] = $request_data;

            return $return_arr;
        }

        if( !($this->_request = new S2P_SDK_Rest_API_Request()) )
        {
            $this->set_error( self::ERR_PREPARE_REQUEST, self::s2p_t( 'Couldn\'t prepare API request.' ) );
            return false;
        }

        if( empty( $request_data['http_method'] )
         or !$this->_request->set_http_method( $request_data['http_method'] ) )
        {
            $this->set_error( self::ERR_HTTP_METHOD, self::s2p_t( 'Couldn\'t set HTTP method.' ) );
            return false;
        }

        if( !$this->_request->set_url( $final_url ) )
        {
            $this->set_error( self::ERR_URL, self::s2p_t( 'Couldn\'t set final URL.' ) );
            return false;
        }

        if( !empty( $request_data['request_body'] ) )
            $this->_request->set_body( $request_data['request_body'] );

        $this->_request->add_header( 'Content-Type', 'application/json; charset=utf-8' );

        $call_params = $params['curl_params'];
        if( empty( $params['user_agent'] ) )
            $call_params['user_agent'] = 'APISDK_'.S2P_SDK_VERSION.'/PHP_'.@phpversion().'/'.@php_uname('s').'_'.@php_uname('r');
        else
            $call_params['user_agent'] = trim( $params['user_agent'] );
        $call_params['userpass'] = array( 'user' => $site_id, 'pass' => $api_key );

        self::trigger_hooks( 'rest_api_call_before', array( 'api_obj' => $this ) );

        if( !($request_result = $this->_request->do_curl( $call_params )) )
            $request_result = null;

        if( ($hook_result = self::trigger_hooks( 'rest_api_request_result', array( 'api_obj' => $this, 'request_result' => $request_result ) ))
        and is_array( $hook_result ) )
        {
            if( array_key_exists( 'request_result', $hook_result ) )
                $request_result = S2P_SDK_Rest_API_Request::validate_request_array( $hook_result['request_result'] );
        }

        if( empty( $request_result ) )
        {
            if( $this->_request->has_error() )
                $this->copy_error( $this->_request );
            else
                $this->set_error( self::ERR_CURL_CALL, self::s2p_t( 'Error sending API call' ) );

            return false;
        }

        $return_arr = self::default_call_result();
        $return_arr['final_url'] = $final_url;
        $return_arr['request'] = $request_result;

        $this->_call_result = $return_arr;

        if( !in_array( $request_result['http_code'], S2P_SDK_Rest_API_Codes::success_codes() ) )
        {
            $code_str = $request_result['http_code'];
            if( ($code_details = S2P_SDK_Rest_API_Codes::valid_code( $request_result['http_code'] )) )
                $code_str .= ' ('.$code_details.')';

            // Set a generic error as maybe we will get more specific errors later when parsing response. Don't throw this error yet...
            $this->set_error( self::ERR_CURL_CALL, self::s2p_t( 'Request responded with error code %s', $code_str ), '', array( 'prevent_throwing_errors' => true ) );

            if( empty( $request_result['response_buffer'] ) )
            {
                // In case there's nothing to parse, throw generic error...
                if( $this->throw_errors() )
                    $this->throw_error();
                return false;
            }
        }

        if( !($response_data = $this->_method->parse_response( $request_result )) )
        {
            if( $this->_method->has_error() )
                $this->copy_error( $this->_method );

            if( $this->has_error() )
            {
                if( $this->throw_errors() )
                    $this->throw_error();
                return false;
            }

            $this->set_error( self::ERR_PARSE_RESPONSE, self::s2p_t( 'Error parsing server response.' ) );

            return false;
        }

        if( ($hook_result = self::trigger_hooks( 'rest_rest_api_response_data', array( 'api_obj' => $this, 'response_data' => $response_data ) ))
        and is_array( $hook_result ) )
        {
            if( array_key_exists( 'response_data', $hook_result ) )
                $response_data = $hook_result['response_data'];
        }

        if( !$this->_method->validate_response( $response_data ) )
        {
            if( $this->_method->has_error() )
                $this->copy_error( $this->_method );
            else
                $this->set_error( self::ERR_VALIDATE_RESPONSE, self::s2p_t( 'Error validating server response.' ) );

            return false;
        }

        $return_arr['response'] = $response_data;

        $this->_call_result = $return_arr;

        // Make sure errors get thrown if any...
        if( $this->has_error()
        and $this->throw_errors() )
            $this->throw_error();

        // Make sure errors get thrown if any...
        if( $this->has_error() )
            return false;

        return $return_arr;
    }

    public function method( $method, $params = null )
    {
        $this->_method = null;

        $method = 'S2P_SDK_Meth_'.ucfirst( strtolower( $method ) );

        /** @var S2P_SDK_Method $method */
        if( !($method_obj = self::get_instance( $method, $params, false )) )
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

}
