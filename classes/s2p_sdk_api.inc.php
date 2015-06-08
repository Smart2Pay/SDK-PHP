<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_CLASSES' ) )
    die( 'Something went bad' );

include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_rest_api.inc.php' );

class S2P_SDK_API extends S2P_SDK_Module
{
    const TYPE_REST = 'rest';

    const ERR_API_TYPE = 1, ERR_API_OBJECT = 2, ERR_API_CALL = 3;

    /** @var string $_api_type */
    private $_api_type = self::TYPE_REST;

    /** @var S2P_SDK_Rest_API $_api */
    private $_api = null;

    /** @var array $_call_result */
    private $_call_result = null;

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

        if( !empty( $module_params['api_key'] ) and !empty( $module_params['method'] )
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

    private function reset_api()
    {
        $this->_api = null;
        $this->_api_type = self::TYPE_REST;
        $this->_call_result = null;
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
     * Return full API call result
     *
     * @return array Return full API call result
     */
    public function get_full_call_result()
    {
        return $this->_call_result;
    }

    /**
     * Return JSON decoded array from server response
     *
     * @return array Return JSON decoded array from server response
     */
    public function get_result()
    {
        if( empty( $this->_call_result ) or !is_array( $this->_call_result )
         or empty( $this->_call_result['response'] ) or !is_array( $this->_call_result['response'] )
         or !isset( $this->_call_result['response']['response_array'] ) or !is_array( $this->_call_result['response']['response_array'] ) )
            return false;

        return $this->_call_result['response']['response_array'];
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
                and !($this->_api = self::get_instance( 'S2P_SDK_Rest_API', $api_params )) )
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

        $this->_call_time = 0;
        $call_start = microtime( true );
        if( !($call_result = $this->_api->do_call( $params )) )
        {
            $this->_call_time = microtime( true ) - $call_start;
            if( $this->_api->has_error() )
                $this->copy_error( $this->_api );

            else
                $this->set_error( self::ERR_API_CALL, self::s2p_t( 'Error in API call.' ) );

            return false;
        }

        $this->_call_time = microtime( true ) - $call_start;

        $this->_call_result = $call_result;

        return $this->_call_result;
    }

}
