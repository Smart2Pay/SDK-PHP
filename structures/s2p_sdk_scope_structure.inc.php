<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_scope_variable.inc.php' );

abstract class S2P_SDK_Scope_Structure extends S2P_SDK_Language
{
    const ERR_JSON = 1, ERR_VARIABLE = 2, ERR_DEFINITION = 3, ERR_MERGE = 4;

    /** @var S2P_SDK_Scope_Variable $_var */
    protected $_var = null;

    /**
     * Structures can be merged to parse responses containing more defined structures or to create requests containing
     * multiple structures Array of S2P_SDK_Scope_Structure objects
     *
     * @var null|array $_merged_structures
     */
    protected $_merged_structures = null;

    /**
     * Function should return array with full variable definition
     * @return array
     */
    abstract public function get_definition();

    /**
     * Function should return structure definition for blobs or array variables
     * @return array
     */
    abstract public function get_structure_definition();

    function __construct()
    {
        parent::__construct();
    }

    public function get_validated_definition()
    {
        if( !($definition_arr = $this->get_definition())
         or !is_array( $definition_arr ) )
        {
            $this->set_error( self::ERR_DEFINITION, self::s2p_t( 'Invalid definition.' ) );
            return false;
        }

        if( !S2P_SDK_Scope_Variable::validate_definition( $definition_arr ) )
        {
            $this->copy_static_error();
            return false;
        }

        return $definition_arr;
    }

    /**
     * Returns structure internal name or false on error.
     *
     * @return string Structure internal name
     */
    function get_name()
    {
        if( !($definition_arr = $this->get_validated_definition()) )
            return false;

        return $definition_arr['name'];
    }

    /**
     * Returns structure internal name or false on error.
     *
     * @return string Structure internal name
     */
    function get_external_name()
    {
        if( !($definition_arr = $this->get_validated_definition()) )
            return false;

        return $definition_arr['external_name'];
    }

    /**
     * Returns validated definition with variable names as keys
     *
     * @return array Structure definition with variable names as keys
     */
    function get_structure_with_keys( $definition_arr = null, $top_level = true )
    {
        static $result_definition = null;

        if( $result_definition !== null )
            return $result_definition;

        if( $definition_arr === null
            and !($definition_arr = $this->get_validated_definition()) )
            return false;

        if( empty( $definition_arr ) or !is_array( $definition_arr ) )
            return null;

        $new_definition = array();
        if( !empty( $definition_arr['structure'] ) )
            $new_definition[$definition_arr['name']] = $this->get_structure_with_keys( $definition_arr['structure'], false );
        else
            $new_definition[$definition_arr['name']] = $definition_arr;

        if( $top_level )
            $result_definition = $new_definition;

        return $new_definition;
    }

    /**
     * Returns validated definition with variable external names as keys
     *
     * @return array Structure definition with variable external names as keys
     */
    function get_structure_with_external_keys( $definition_arr = null, $top_level = true )
    {
        static $result_definition = null;

        if( $result_definition !== null )
            return $result_definition;

        if( $definition_arr === null
            and !($definition_arr = $this->get_validated_definition()) )
            return false;

        if( empty( $definition_arr ) or !is_array( $definition_arr ) )
            return null;

        $new_definition = array();
        if( !empty( $definition_arr['structure'] ) )
            $new_definition[$definition_arr['external_name']] = $this->get_structure_with_keys( $definition_arr['structure'], false );
        else
            $new_definition[$definition_arr['external_name']] = $definition_arr;

        if( $top_level )
            $result_definition = $new_definition;

        return $new_definition;
    }

    /**
     * Merge a structure with current one to form a request or to parse a response
     * Order of merged structures will determine order of keys in resulting JSON
     *
     * @param S2P_SDK_Scope_Structure $structure
     */
    public function merge_structure( $structure, $for_request = false )
    {
        if( empty( $structure ) or !($structure instanceof S2P_SDK_Scope_Structure) )
            return false;

        if( !empty( $for_request ) )
            $key = $this->get_external_name();
        else
            $key = $this->get_name();

        if( empty( $key ) )
        {
            $this->set_error( self::ERR_MERGE, self::s2p_t( 'Couldn\'t extract structure name.' ) );
            return false;
        }

        if( empty( $this->_merged_structures ) or !is_array( $this->_merged_structures ) )
            $this->_merged_structures = array();

        $this->_merged_structures[$key] = $structure;

        return true;
    }

    private function _init_variable()
    {
        if( $this->_var === null )
            $this->_var = new S2P_SDK_Scope_Variable( $this->get_definition() );

        if( empty( $this->_var ) )
        {
            $this->_var = null;
            $this->set_error( self::ERR_VARIABLE, self::s2p_t( 'Couldn\'t initialize parsing variable' ) );
            return null;
        }

        return $this->_var;
    }

    /**
     * Parses a string buffer from server response
     *
     * @param string $scope_buf
     *
     * @return array|bool|mixed
     */
    public function extract_info_from_response_buffer( $scope_buf, $parsing_params = false )
    {
        if( !($scope_arr = @json_decode( $scope_buf, true ))
         or !is_array( $scope_arr ) )
        {
            $this->set_error( self::ERR_JSON, self::s2p_t( 'Couldn\'t decode JSON buffer.'  ) );
            return false;
        }

        return $this->extract_info_from_response_array( $scope_arr, $parsing_params );
    }

    /**
     * Transform array keys from external_name to name and vice-versa.
     *
     * @param array $scope_arr Array to transform
     *
     * @return array|bool|mixed
     */
    public function transfrom_keys_to_external_names( $scope_arr, $parsing_params = false )
    {
        if( !$this->_init_variable() )
            return false;

        if( empty( $parsing_params ) or !is_array( $parsing_params ) )
            $parsing_params = array();

        $parsing_params['check_external_names'] = false;

        if( !($return_arr = $this->_var->transform_keys( $scope_arr, null, $parsing_params ))
         or !is_array( $return_arr ) )
            $return_arr = false;

        if( !empty( $this->_merged_structures ) and is_array( $this->_merged_structures ) )
        {
            if( empty( $return_arr ) )
                $return_arr = array();

            /**
             * @var string $structure_name
             * @var S2P_SDK_Scope_Structure $structure_obj
             */
            foreach( $this->_merged_structures as $structure_name => $structure_obj )
            {
                if( !($structure_result_arr = $structure_obj->transfrom_keys_to_external_names( $scope_arr, $parsing_params ))
                 or !is_array( $structure_result_arr ) )
                    continue;

                $return_arr = array_merge( $return_arr, $structure_result_arr );
            }
        }

        return $return_arr;
    }

    /**
     * Transform array keys from external_name to name and vice-versa.
     *
     * @param array $scope_arr Array to transform
     *
     * @return array|bool|mixed
     */
    public function transfrom_keys_to_internal_names( $scope_arr, $parsing_params = false )
    {
        if( !$this->_init_variable() )
            return false;

        if( empty( $parsing_params ) or !is_array( $parsing_params ) )
            $parsing_params = array();

        $parsing_params['check_external_names'] = true;

        if( !($return_arr = $this->_var->transform_keys( $scope_arr, null, $parsing_params ))
         or !is_array( $return_arr ) )
            $return_arr = false;

        if( !empty( $this->_merged_structures ) and is_array( $this->_merged_structures ) )
        {
            if( empty( $return_arr ) )
                $return_arr = array();

            /**
             * @var string $structure_name
             * @var S2P_SDK_Scope_Structure $structure_obj
             */
            foreach( $this->_merged_structures as $structure_name => $structure_obj )
            {
                if( !($structure_result_arr = $structure_obj->transfrom_keys_to_internal_names( $scope_arr, $parsing_params ))
                 or !is_array( $structure_result_arr ) )
                    continue;

                $return_arr = array_merge( $return_arr, $structure_result_arr );
            }
        }

        return $return_arr;
    }

    /**
     * Parses an array which was obtained from a json_decode from a server response body (or an emulated array ;) )
     * In case of parsing errors we try to extract as much information as possible from array.
     *
     * @param array $scope_arr
     *
     * @return array|bool|mixed
     */
    public function extract_info_from_response_array( $scope_arr, $parsing_params = false )
    {
        if( !$this->_init_variable() )
            return false;

        if( empty( $parsing_params ) or !is_array( $parsing_params ) )
            $parsing_params = array();

        $parsing_params['check_external_names'] = true;

        if( !($return_arr = $this->_var->extract_values( $scope_arr, $parsing_params ))
         or !is_array( $return_arr ) )
            $return_arr = false;

        if( !empty( $this->_merged_structures ) and is_array( $this->_merged_structures ) )
        {
            if( empty( $return_arr ) )
                $return_arr = array();

            /**
             * @var string $structure_name
             * @var S2P_SDK_Scope_Structure $structure_obj
             */
            foreach( $this->_merged_structures as $structure_name => $structure_obj )
            {
                if( !($structure_result_arr = $structure_obj->extract_info_from_response_array( $scope_arr, $parsing_params ))
                 or !is_array( $structure_result_arr ) )
                {
                    $this->copy_parsing_error( $structure_obj );
                    continue;
                }

                if( $structure_obj->get_parsing_error() )
                    $this->copy_parsing_error( $structure_obj );

                $return_arr = array_merge( $return_arr, $structure_result_arr );
            }
        }

        return $return_arr;
    }

    /**
     * Parses an array of information and formats it according to structure definition using external_names as keys
     * If result has errors there is no use to send buggy request so we just return false.
     *
     * @param array $info_arr
     *
     * @return array|bool
     */
    public function prepare_info_for_request_to_array( $info_arr, $parsing_params = false )
    {
        if( !$this->_init_variable() )
            return false;

        if( empty( $parsing_params ) or !is_array( $parsing_params ) )
            $parsing_params = array();

        $parsing_params['check_external_names'] = false;

        if( !($return_arr = $this->_var->extract_values( $info_arr, $parsing_params ))
         or $this->_var->has_error() )
            $return_arr = false;

        if( !empty( $this->_merged_structures ) and is_array( $this->_merged_structures ) )
        {
            if( empty( $return_arr ) )
                $return_arr = array();

            /**
             * @var string $structure_name
             * @var S2P_SDK_Scope_Structure $structure_obj
             */
            foreach( $this->_merged_structures as $structure_name => $structure_obj )
            {
                if( !($structure_result_arr = $structure_obj->prepare_info_for_request_to_array( $info_arr, $parsing_params ))
                 or !is_array( $structure_result_arr ) )
                {
                    $this->copy_parsing_error( $structure_obj );
                    continue;
                }

                if( $structure_obj->get_parsing_error() )
                    $this->copy_parsing_error( $structure_obj );

                $return_arr = array_merge( $return_arr, $structure_result_arr );
            }
        }

        return $return_arr;
    }

    public function prepare_info_for_request_to_buffer( $info_arr, $parsing_params = false )
    {
        if( !($parsed_arr = $this->prepare_info_for_request_to_array( $info_arr, $parsing_params ))
         or !is_array( $parsed_arr ) )
            return false;

        return @json_encode( $parsed_arr );
    }

    /**
     * Returns false if there were no errors in variable or error array if any errors in $_var object
     *
     * @return array|bool
     */
    public function get_parsing_error()
    {
        $this->_init_variable();

        if( !$this->has_error() and !$this->_var->has_error() )
            return false;

        return ($this->has_error()?$this->get_error():$this->_var->get_error());
    }

    /**
     * Copies any parsing error from $obj to current object
     *
     * @param S2P_SDK_Scope_Structure $obj
     */
    public function copy_parsing_error( $obj )
    {
        if( empty( $obj ) or !($obj instanceof S2P_SDK_Scope_Structure)
         or !($error_arr = $obj->get_parsing_error()) )
            return false;

        $this->copy_error_from_array( $error_arr );

        return true;
    }

}