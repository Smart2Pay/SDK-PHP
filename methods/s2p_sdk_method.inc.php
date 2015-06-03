<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_scope_variable.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_scope_structure.inc.php' );

abstract class S2P_SDK_Method extends S2P_SDK_Language
{
    const ERR_NAME = 1, ERR_GET_VARIABLES = 2, ERR_REQUEST_STRUCTURE = 3, ERR_RESPONSE_STRUCTURE = 4, ERR_MANDATORY = 5, ERR_FUNCTIONALITY = 6;
    /**
     * Variable which holds all details regarding method
     * @var string $_definition
     **/
    protected $_definition = null;

    /**
     * Some methods can have variations which change suffix API call
     *
     * @var int $_functionality
     */
    protected $_functionality = 0;

    /**
     * Child class should return an array with possible functionalities current method have
     * @return array
     */
    abstract public function get_functionalities();

    abstract public function default_functionality();

    function __construct( $params = false )
    {
        parent::__construct();

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['functionality'] ) or !$this->valid_functionality( $params['functionality'] ) )
            $params['functionality'] = $this->default_functionality();

        $this->method_functionality( $params['functionality'] );
    }

    public function method_functionality( $func )
    {
        if( !$this->valid_functionality( $func ) )
        {
            $this->set_error( self::ERR_FUNCTIONALITY, self::s2p_t( 'Invalid functionality provided.' ) );
            return false;
        }

        $this->_functionality = $func;
        return $this->_functionality;
    }

    /**
     * Returns array which defines method depending on current functionality
     *
     * @return array
     */
    public function get_method_definition()
    {
        $functionality = $this->_functionality;
        if( empty( $functionality )
         or !($method_definition = $this->valid_functionality( $functionality ))
         or !is_array( $method_definition ) )
        {
            $this->set_error( self::ERR_FUNCTIONALITY, self::s2p_t( 'Functionality not set.' ) );
            return false;
        }

        return $method_definition;
    }

    /**
     * Override method funcitonality array with returning array
     *
     * @param int $func
     *
     * @return bool|array Returns functionality array which will override default method definition array
     */
    public function valid_functionality( $func )
    {
        $func = intval( $func );
        if( !($all_functionalities = $this->get_functionalities())
         or !is_array( $all_functionalities ) or empty( $all_functionalities[$func] ) )
            return false;

        return $all_functionalities[$func];
    }

    public function get_name()
    {
        if( !$this->validate_definition() )
            return false;

        return $this->_definition['name'];
    }

    /**
     * Prepare query string and request body using variables to be sent in get and request_structure to be sent as JSON in body
     * from method definition.
     *
     * @param bool $params Receives parameters to be used in GET and scope array to create JSON body after parsing $_definition['request_structure'] object
     *
     * @return array|bool Returns an array with data to be used in request to server or false on error
     */
    public function prepare_for_request( $params = false )
    {
        if( !$this->validate_definition() )
            return false;

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['get_variables'] ) or !is_array( $params['get_variables'] ) )
            $params['get_variables'] = array();
        if( empty( $params['method_params'] ) or !is_array( $params['method_params'] ) )
            $params['method_params'] = array();

        $return_arr = array();
        $return_arr['full_query'] = $this->_definition['url_suffix'];
        $return_arr['query_string'] = '';
        $return_arr['get_variables'] = array();
        $return_arr['request_body'] = '';

        if( !empty( $this->_definition['get_variables'] ) and is_array( $this->_definition['get_variables'] ) )
        {
            foreach( $this->_definition['get_variables'] as $get_var )
            {
                if( !array_key_exists( $get_var['name'], $params['get_variables'] ) )
                {
                    if( !empty( $get_var['mandatory'] ) )
                    {
                        $this->set_error( self::ERR_MANDATORY, self::s2p_t( 'Variable %s is mandatory for method %s.', $get_var['name'], $this->_definition['name'] ) );
                        return false;
                    }

                    continue;
                }

                $return_arr['get_variables'][$get_var['name']] = S2P_SDK_Scope_Variable::scalar_value( $get_var['type'], $params['get_variables'][$get_var['name']] );
            }
        }

        if( !empty( $return_arr['get_variables'] ) )
            $return_arr['query_string'] = http_build_query( $return_arr['get_variables'] );

        if( !empty( $return_arr['query_string'] ) )
        {
            if( strstr( $return_arr['full_query'], '?' ) === false )
                $return_arr['full_query'] .= '?';

            $return_arr['full_query'] .= $return_arr['query_string'];
        }

        if( !empty( $this->_definition['request_structure'] ) )
        {
            /** @var S2P_SDK_Scope_Structure $request_structure */
            $request_structure = $this->_definition['request_structure'];

            if( !($json_body = $request_structure->prepare_info_for_request_to_buffer( $params['method_params'], array( 'output_null_values' => false ) )) )
            {
                $this->copy_error_from_array( $request_structure->get_parsing_error() );
                return false;
            }

            $return_arr['request_body'] = $json_body;
        }

        return $return_arr;
    }

    public static function default_get_variables_definition()
    {
        return array(
            // name of variable
            'name' => '',
            // S2P_SDK_Scope_Variable::TYPE_*. Resulting value will be validated through S2P_SDK_Scope_Variable::scalar_value()
            'type' => 0,
            'default' => '',
            'mandatory' => false,
        );
    }

    public static function validate_get_variable_definition( $definition_arr )
    {
        self::st_reset_error();

        if( empty( $definition_arr ) or !is_array( $definition_arr )
         or empty( $definition_arr['name'] ) )
        {
            self::st_set_error( self::ERR_GET_VARIABLES, self::s2p_t( 'Name or structure invalid for get variable.' ) );
            return false;
        }

        $default_definition = self::default_get_variables_definition();

        $new_definition_arr = array();
        foreach( $default_definition as $key => $def_value )
        {
            if( ! array_key_exists( $key, $definition_arr ) )
                $new_definition_arr[ $key ] = $def_value;
            else
                $new_definition_arr[ $key ] = $definition_arr[ $key ];
        }

        if( empty( $new_definition_arr['type'] ) or !S2P_SDK_Scope_Variable::valid_type( $new_definition_arr['type'] ) )
        {
            self::st_set_error( self::ERR_GET_VARIABLES, self::s2p_t( 'Invalid type for variable %s.', $new_definition_arr['name'] ) );
            return false;
        }

        return $new_definition_arr;
    }

    public static function default_method_definition()
    {
        return array(
            'name' => '',
            'url_suffix' => '',
            // array of parameters which should be parsed in GET for the request
            // Key should be name of variable which will be parsed in url_suffix string
            'get_variables' => null,

            // Array with keys representing mandatory properties in request structure (value doesn't matter)
            'mandatory_in_request' => null,

            // Structure to be passed to server in request body
            'request_structure' => null,

            // Array with keys representing mandatory properties in response structure (value doesn't matter)
            'mandatory_in_response' => null,

            // Structure to be expected back from server at response
            'response_structure' => null,
        );
    }

    private function validate_definition()
    {
        if( !is_null( $this->_definition ) )
            return true;

        $default_definition = self::default_method_definition();

        $definition_arr = $this->get_method_definition();

        $new_definition_arr = array();
        foreach( $default_definition as $key => $def_value )
        {
            if( !array_key_exists( $key, $definition_arr ) )
                $new_definition_arr[ $key ] = $def_value;
            else
                $new_definition_arr[ $key ] = $definition_arr[ $key ];
        }

        if( empty( $new_definition_arr['name'] ) )
        {
            $this->set_error( self::ERR_NAME, self::s2p_t( 'You should provide a name in method definition.' ) );
            return false;
        }

        if( !empty( $new_definition_arr['get_variables'] ) )
        {
            if( !is_array( $new_definition_arr['get_variables'] ) )
            {
                $this->set_error( self::ERR_GET_VARIABLES, self::s2p_t( 'Invalid get variables for method %s.', $new_definition_arr['name'] ) );
                return false;
            }

            $new_var_definition_arr = array();
            foreach( $new_definition_arr['get_variables'] as $key => $var_definition )
            {
                if( !($new_definition = self::validate_get_variable_definition( $var_definition )) )
                {
                    $this->copy_static_error();
                    continue;
                }

                $new_var_definition_arr[$key] = $new_definition;
            }

            if( empty( $new_var_definition_arr ) )
                $new_var_definition_arr = null;

            $new_definition_arr['get_variables'] = $new_var_definition_arr;
        }

        /** @var S2P_SDK_Scope_Structure $new_definition_arr['request_structure'] */
        if( empty( $new_definition_arr['request_structure'] ) )
            $new_definition_arr['request_structure'] = null;

        elseif( !($new_definition_arr['request_structure'] instanceof S2P_SDK_Scope_Structure) )
        {
            $this->set_error( self::ERR_REQUEST_STRUCTURE, self::s2p_t( 'Invalid request structure object for method %s.', $new_definition_arr['name'] ) );
            return false;
        } elseif( !$new_definition_arr['request_structure']->get_validated_definition() )
        {
            $this->copy_error( $new_definition_arr['request_structure'] );
            return false;
        }

        /** @var S2P_SDK_Scope_Structure $new_definition_arr['response_structure'] */
        if( empty( $new_definition_arr['response_structure'] ) )
            $new_definition_arr['response_structure'] = null;

        elseif( !($new_definition_arr['response_structure'] instanceof S2P_SDK_Scope_Structure) )
        {
            $this->set_error( self::ERR_RESPONSE_STRUCTURE, self::s2p_t( 'Invalid response structure object for method %s.', $new_definition_arr['name'] ) );
            return false;
        } elseif( !$new_definition_arr['response_structure']->get_validated_definition() )
        {
            $this->copy_error( $new_definition_arr['response_structure'] );
            return false;
        }

        $this->_definition = $new_definition_arr;

        return true;
    }
}