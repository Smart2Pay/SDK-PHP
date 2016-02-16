<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_CLASSES' ) or !defined( 'S2P_SDK_DIR_METHODS' ) )
    die( 'Something went bad' );

include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_helper.inc.php' );
include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_rest_api.inc.php' );
include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_method.inc.php' );

class S2P_SDK_API extends S2P_SDK_Module
{
    const TYPE_REST = 'rest';

    const ERR_API_TYPE = 1, ERR_API_OBJECT = 2, ERR_API_CALL = 3, ERR_INPUT = 4;

    /** @var string $_api_type */
    private $_api_type = self::TYPE_REST;

    /** @var S2P_SDK_Rest_API $_api */
    private $_api = null;

    /** @var array $_finalize_result */
    private $_finalize_result = null;

    /** @var float $_call_time */
    private $_call_time = 0;

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

        if( empty( $module_params['api_type'] ) or !self::valid_api_type( $module_params['api_type'] ) )
            $module_params['api_type'] = self::TYPE_REST;

        if( !$this->api_type( $module_params['api_type'] ) )
            return false;

        $api_config_arr = self::get_api_configuration();

        if( empty( $module_params['site_id'] ) and !empty( $api_config_arr['site_id'] ) )
            $module_params['site_id'] = $api_config_arr['site_id'];
        if( empty( $module_params['api_key'] ) and !empty( $api_config_arr['api_key'] ) )
            $module_params['api_key'] = $api_config_arr['api_key'];
        if( empty( $module_params['environment'] ) and !empty( $api_config_arr['environment'] ) )
            $module_params['environment'] = $api_config_arr['environment'];

        if( !empty( $module_params['api_key'] )
        and !empty( $module_params['site_id'] )
        and !empty( $module_params['method'] )
        and !$this->create_api_object( $module_params ) )
            return false;

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

    private function reset_api( $full_reset = true )
    {
        if( !empty( $full_reset ) )
        {
            $this->_api      = null;
            $this->_api_type = self::TYPE_REST;
        }

        $this->_call_time = 0;
    }

    public static function valid_api_type( $type )
    {
        if( !in_array( $type, array( self::TYPE_REST ) ) )
            return false;

        return true;
    }

    public function api_type( $type = null )
    {
        if( $type === null )
            return $this->_api_type;

        if( !in_array( $type, array( self::TYPE_REST ) ) )
        {
            $this->set_error( self::ERR_API_TYPE,
                                  self::s2p_t( 'Unknown API type' ),
                                  sprintf( 'Unknown API type [%s]', $type ) );
            return false;
        }

        $this->_api_type = $type;
        return true;
    }

    /**
     * Return current API object
     *
     * @return S2P_SDK_Rest_API Returns API object
     */
    public function get_api_obj()
    {
        return $this->_api;
    }

    /**
     * Returns how many microseconds were spent on API call
     *
     * @return int Returns API call time
     */
    public function get_call_time()
    {
        return $this->_call_time;
    }

    /**
     * Return JSON decoded array from server response
     *
     * @return array Return JSON decoded array from server response
     */
    public function get_result()
    {
        if( empty( $this->_api ) )
            return false;

        $call_result = $this->_api->get_call_result();

        if( empty( $call_result ) or !is_array( $call_result )
         or empty( $call_result['response'] ) or !is_array( $call_result['response'] )
         or !isset( $call_result['response']['response_array'] ) or !is_array( $call_result['response']['response_array'] ) )
            return false;

        return $call_result['response']['response_array'];
    }

    private function create_api_object( $api_params = false )
    {
        $type = $this->api_type();

        switch( $type )
        {
            default:
                $this->set_error( self::ERR_API_TYPE,
                                        self::s2p_t( 'Unknown API type' ),
                                        sprintf( 'Unknown API type [%s]', $type ) );

                return false;

            case self::TYPE_REST:
                if( empty( $this->_api )
                and !($this->_api = self::get_instance( 'S2P_SDK_Rest_API', $api_params, false )) )
                {
                    if( self::st_has_error() )
                        $this->copy_static_error();

                    else
                        $this->set_error( self::ERR_API_OBJECT, self::s2p_t( 'Couldn\'t initiate API object.' ) );

                    return false;
                }
            break;
        }

        return true;
    }

    public function do_call( $params = false )
    {
        if( !$this->create_api_object( $params ) )
        {
            $this->set_error( self::ERR_API_OBJECT, self::s2p_t( 'Couldn\'t initiate API object.' ) );
            return false;
        }

        $this->reset_api( false );

        $this->_call_time = 0;
        $call_start = microtime( true );
        if( !($call_result = $this->_api->do_call( $params )) )
        {
            self::reset_one_call_settings();

            $this->_call_time = microtime( true ) - $call_start;
            if( $this->_api->has_error() )
                $this->copy_error( $this->_api );

            else
                $this->set_error( self::ERR_API_CALL, self::s2p_t( 'Error in API call.' ) );

            return false;
        }

        self::reset_one_call_settings();

        $this->_call_time = microtime( true ) - $call_start;

        return $call_result;
    }

    public function do_finalize( $params = false )
    {
        if( empty( $this->_api ) )
        {
            $this->set_error( self::ERR_API_OBJECT, self::s2p_t( 'Couldn\'t finalize, API object is empty.' ) );
            return false;
        }

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        // If redirect is required, send redirect headers now...
        if( !isset( $params['redirect_now'] ) )
            $params['redirect_now'] = true;

        if( !($finalize_result = $this->_api->do_finalize( $params ))
         or !($finalize_result = S2P_SDK_Method::validate_finalize_result( $finalize_result )) )
        {
            if( $this->_api->has_error() )
                $this->copy_error( $this->_api );

            else
                $this->set_error( self::ERR_API_CALL, self::s2p_t( 'Couldn\'t finialize API action.' ) );

            return false;
        }

        if( !empty( $params['redirect_now'] )
        and !empty( $finalize_result['should_redirect'] ) and !empty( $finalize_result['redirect_to'] )
        and empty( $finalize_result['redirect_headers_set'] ) )
        {
            if( !@headers_sent() )
            {
                @header( 'Location: '.$finalize_result['redirect_to'] );

                $finalize_result['redirect_headers_set'] = true;
            }
        }

        $this->_finalize_result = $finalize_result;

        return $this->_finalize_result;
    }

}
