<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_STRUCTURES' ) or !defined( 'S2P_SDK_DIR_CLASSES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_scope_variable.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_scope_structure.inc.php' );
include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_rest_api_request.inc.php' );

abstract class S2P_SDK_Method extends S2P_SDK_Module
{
    const ERR_NAME = 1, ERR_GET_VARIABLES = 2, ERR_REQUEST_STRUCTURE = 3, ERR_RESPONSE_STRUCTURE = 4, ERR_MANDATORY = 5, ERR_FUNCTIONALITY = 6,
          ERR_REQUEST_DATA = 7, ERR_REQUEST_MANDATORY = 8, ERR_HTTP_METHOD = 9;
    /**
     * Variable which holds all details regarding method
     * @var string $_definition
     **/
    protected $_definition = null;

    /**
     * Tells what functionality will be implemented for current method
     *
     * @var string $_functionality
     */
    protected $_functionality = '';

    /**
     * Parameters to be used when generating request data
     *
     * @var array $_request_params
     */
    protected $_request_params = array();

    /**
     * Child class should return an array with possible functionalities current method have
     * @return array
     */
    abstract public function get_functionalities();

    /**
     * Returns default functionality
     * @return string
     */
    abstract public function default_functionality();

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed
     */
    public function init( $module_params = false )
    {
        $this->reset_method();

        if( empty( $module_params ) or !is_array( $module_params ) )
            $module_params = array();

        if( empty( $module_params['func'] ) or !$this->valid_functionality( $module_params['func'] ) )
            $module_params['func'] = $this->default_functionality();

        if( $this->method_functionality( $module_params['func'] ) === false )
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
        $this->reset_method();
    }

    function __construct( $params = false )
    {
        parent::__construct( $params );
    }

    protected function reset_method()
    {
        $this->_definition = null;
        $this->_functionality = '';
        $this->_request_params = self::default_request_parameters();
    }

    public function get_method_functionality()
    {
        return $this->_functionality;
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
     * @param string $func
     *
     * @return bool|array Returns functionality array which will override default method definition array
     */
    public function valid_functionality( $func )
    {
        $func = trim( strtolower( $func ) );
        if( !($all_functionalities = $this->get_functionalities())
         or !is_array( $all_functionalities ) or empty( $all_functionalities[$func] ) )
            return false;

        return $all_functionalities[$func];
    }

    public function get_available_functionalities()
    {
        if( !($functionalities_arr = $this->get_functionalities())
         or !is_array( $functionalities_arr ) )
            return false;

        $functionalities = array();
        foreach( $functionalities_arr as $func => $func_arr )
        {
            $available_func_arr = array();
            $available_func_arr['mandatory_get_variables'] = array();
            $available_func_arr['get_variables'] = array();
            $available_func_arr['mandatory_method_params'] = array();
            $available_func_arr['method_params'] = array();

            if( !empty( $func_arr['get_variables'] ) and is_array( $func_arr['get_variables'] ) )
            {
                foreach( $func_arr['get_variables'] as $get_var_arr )
                {
                    if( !empty( $get_var_arr['mandatory'] ) )
                        $available_func_arr['mandatory_get_variables'][] = $get_var_arr['name'];

                    $available_func_arr['get_variables'][$get_var_arr['name']] = $get_var_arr['default'];
                }
            }

            if( !empty( $func_arr['request_structure'] ) )
            {
                /** @var S2P_SDK_Scope_Structure $request_structure */
                $request_structure = $func_arr['request_structure'];

                if( !empty( $func_arr['mandatory_in_request'] ) )
                    $request_arr = $func_arr['mandatory_in_request'];
                else
                    $request_arr = array();

                $available_func_arr['mandatory_method_params'] = $request_structure->extract_info_from_response_array( $request_arr );

                $extraction_arr = array();
                $extraction_arr['nullify_full_object'] = true;
                $extraction_arr['skip_regexps'] = true;

                $available_func_arr['method_params'] = $request_structure->extract_info_from_response_array( $request_arr, $extraction_arr );
            }

            $functionalities[$func] = $available_func_arr;
        }

        return array(
            'default_functionality' => $this->default_functionality(),
            'functionalities' => $functionalities,
        );
    }

    public function get_name()
    {
        if( !$this->validate_definition() )
            return false;

        return $this->_definition['name'];
    }

    public static function default_request_parameters()
    {
        return array(
            'get_variables' => array(),
            'method_params' => array(),
        );
    }

    /**
     * Set or retrieve parameters to be used when calculating request data
     *
     * @param null|array $params
     *
     * @return array|bool
     */
    public function request_parameters( $params = null )
    {
        if( $params === null )
            return $this->_request_params;

        if( empty( $params ) or !is_array( $params ) )
            return false;

        $default_parameters = self::default_request_parameters();
        foreach( $default_parameters as $key => $def_value )
        {
            if( array_key_exists( $key, $params ) )
                $this->_request_params[$key] = $params[$key];
        }

        return true;
    }

    /**
     * Prepare query string and request body using variables to be sent in get and request_structure to be sent as JSON in body
     * from method definition.
     *
     * @param array $request_result Array returned by S2P_SDK_Rest_API_Request::do_curl() call
     *
     * @return array|bool Returns an array with parsed data from response buffer or false on error
     */
    public function parse_response( $request_result )
    {
        if( ! $this->validate_definition() )
            return false;

        $return_arr = array();
        $return_arr['func'] = $this->_functionality;
        $return_arr['response_array'] = array();

        if( !($request_result = S2P_SDK_Rest_API_Request::validate_response_array( $request_result ))
         or $request_result['response_buffer'] == '' )
            return $return_arr;

        if( !empty( $this->_definition['response_structure'] ) )
        {
            /** @var S2P_SDK_Scope_Structure $response_structure */
            $response_structure = $this->_definition['response_structure'];

            if( !($json_array = $response_structure->extract_info_from_response_buffer( $request_result['response_buffer'], array( 'output_null_values' => true ) ))
             or !is_array( $json_array ) )
            {
                if( ($parsing_error = $response_structure->get_parsing_error()) )
                    $this->copy_error_from_array( $parsing_error );

                else
                    $this->set_error( self::ERR_REQUEST_DATA, self::s2p_t( 'Couldn\'t extract respose data or response data is empty.' ) );

                return false;
            }

            if( !empty( $this->_definition['mandatory_in_response'] ) and is_array( $this->_definition['mandatory_in_response'] ) )
            {
                if( !$this->check_mandatory_fields( $json_array, $this->_definition['mandatory_in_response'], array( 'scope_arr_type' => 'response' ) ) )
                {
                    if( !$this->has_error() )
                        $this->set_error( self::ERR_REQUEST_MANDATORY, self::s2p_t( 'Mandatory fields not found in response.' ) );

                    return false;
                }
            }

            $return_arr['response_array'] = $json_array;
        }

        return $return_arr;
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

        if( ($internal_params = $this->request_parameters()) )
        {
            if( !empty( $internal_params['get_variables'] ) and is_array( $internal_params['get_variables'] ) )
                $params['get_variables'] = array_merge( $internal_params['get_variables'], $params['get_variables'] );
            if( !empty( $internal_params['method_params'] ) and is_array( $internal_params['method_params'] ) )
                $params['method_params'] = array_merge( $internal_params['method_params'], $params['method_params'] );
        }

        $return_arr = array();
        $return_arr['func'] = $this->_functionality;
        $return_arr['full_query'] = $this->_definition['url_suffix'];
        $return_arr['http_method'] = $this->_definition['http_method'];
        $return_arr['query_string'] = '';
        $return_arr['url_variables'] = array();
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

                $var_value = S2P_SDK_Scope_Variable::scalar_value( $get_var['type'], $params['get_variables'][$get_var['name']] );

                if( !empty( $get_var['move_in_url'] ) )
                    $return_arr['url_variables'][$get_var['name']] = $var_value;
                else
                    $return_arr['get_variables'][$get_var['name']] = $var_value;
            }
        }

        // Replace any URL variables, even if we don't currently have $return_arr['url_variables'] set
        if( ($replacement_result = $this->replace_url_variables( $return_arr['full_query'], $return_arr['url_variables'] )) )
        {
            if( !empty( $replacement_result['url_variables'] ) and is_array( $replacement_result['url_variables'] ) )
                $return_arr['get_variables'] = array_merge( $return_arr['get_variables'], $replacement_result['url_variables'] );

            $return_arr['full_query'] = $replacement_result['url'];
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

            if( !($json_array = $request_structure->prepare_info_for_request_to_array( $params['method_params'], array( 'output_null_values' => false, 'nullify_full_object' => false ) ))
             or !is_array( $json_array ) )
            {
                if( ($parsing_error = $request_structure->get_parsing_error()) )
                    $this->copy_error_from_array( $parsing_error );

                else
                    $this->set_error( self::ERR_REQUEST_DATA, self::s2p_t( 'Couldn\'t extract request data or request data is empty.' ) );

                return false;
            }

            if( !empty( $this->_definition['mandatory_in_request'] ) and is_array( $this->_definition['mandatory_in_request'] ) )
            {
                if( !$this->check_mandatory_fields( $json_array, $this->_definition['mandatory_in_request'], array( 'scope_arr_type' => 'request' ) ) )
                {
                    if( !$this->has_error() )
                        $this->set_error( self::ERR_REQUEST_MANDATORY, self::s2p_t( 'Mandatory fields not found in request.' ) );

                    return false;
                }
            }

            $return_arr['request_body'] = @json_encode( $json_array );
        }

        var_dump( $return_arr );

        return $return_arr;
    }

    public function default_url_variables()
    {
        return array(
            '{*ID*}' => array(
                'default' => 0,
                'key' => 'id',
            ),
        );
    }

    public function replace_url_variables( $url, $url_variables )
    {
        if( empty( $url ) )
            return false;

        if( empty( $url_variables ) or !is_array( $url_variables ) )
            $url_variables = array();

        $default_variables = self::default_url_variables();
        foreach( $default_variables as $var => $var_arr )
        {
            if( empty( $var_arr ) or !isset( $var_arr['key'] )
             or strstr( $url, $var ) === false )
                continue;

            if( array_key_exists( $var_arr['key'], $url_variables ) )
                $var_value = $url_variables[$var_arr['key']];
            else
                $var_value = (!empty( $var_arr['default'])?$var_arr['default']:'');

            $url = str_replace( $var, $var_value, $url );

            if( isset( $url_variables[$var_arr['key']] ) )
                unset( $url_variables[$var_arr['key']] );
        }

        if( empty( $url_variables ) )
            $url_variables = array();

        $return_arr = array();
        $return_arr['url'] = $url;
        $return_arr['url_variables'] = $url_variables;

        return $return_arr;
    }

    /**
     * Checks if mandatory fields are set in $scope_arr array (output/input array)
     *
     * @param array $scope_arr
     * @param array $mandatory_fields_arr
     *
     * @return bool Returns true if all mandatory fields are found in $scope_arr or false otherwise
     */
    protected function check_mandatory_fields( $scope_arr, $mandatory_fields_arr, $params = false )
    {
        if( empty( $mandatory_fields_arr ) or !is_array( $mandatory_fields_arr ) )
            return true;

        if( empty( $scope_arr ) or !is_array( $scope_arr ) )
            return false;

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['path'] ) )
            $params['path'] = '';
        if( empty( $params['scope_arr_type'] ) )
            $params['scope_arr_type'] = 'request';

        foreach( $mandatory_fields_arr as $key => $fields )
        {
            $current_path = $params['path'].(($params['path'] != '')?'.':'').$key;

            if( !array_key_exists( $key, $scope_arr ) )
            {
                $this->set_error( self::ERR_REQUEST_MANDATORY, self::s2p_t( 'Mandatory field [%s] not found in %s.', $current_path, $params['scope_arr_type'] ) );
                return false;
            }

            if( is_array( $fields ) )
            {
                $new_params = $params;
                $new_params['path'] = $current_path;

                if( !$this->check_mandatory_fields( $scope_arr[$key], $fields, $new_params ) )
                    return false;
            }
        }

        return true;
    }

    protected static function default_get_variables_definition()
    {
        return array(
            // name of variable
            'name' => '',
            // S2P_SDK_Scope_Variable::TYPE_*. Resulting value will be validated through S2P_SDK_Scope_Variable::scalar_value()
            'type' => 0,
            'default' => '',
            'mandatory' => false,
            'move_in_url' => false,
        );
    }

    protected static function validate_get_variable_definition( $definition_arr )
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

    protected static function default_method_definition()
    {
        return array(
            'name' => '',
            'url_suffix' => '',
            'http_method' => 'GET',
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

        if( empty( $new_definition_arr['http_method'] ) or !S2P_SDK_Rest_API_Request::valid_http_method( $new_definition_arr['http_method'] ) )
        {
            $this->set_error( self::ERR_HTTP_METHOD, self::s2p_t( 'Invalid HTTP method for API method %s', $new_definition_arr['name'] ) );
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