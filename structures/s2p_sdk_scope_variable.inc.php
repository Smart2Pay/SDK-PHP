<?php
namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_PATH' ) )
    die( 'Something went bad' );

if( !defined( 'S2P_SDK_VTYPE_STRING' ) )
    define( 'S2P_SDK_VTYPE_STRING', 1 );
if( !defined( 'S2P_SDK_VTYPE_INT' ) )
    define( 'S2P_SDK_VTYPE_INT', 2 );
if( !defined( 'S2P_SDK_VTYPE_FLOAT' ) )
    define( 'S2P_SDK_VTYPE_FLOAT', 3 );
if( !defined( 'S2P_SDK_VTYPE_BOOL' ) )
    define( 'S2P_SDK_VTYPE_BOOL', 4 );
if( !defined( 'S2P_SDK_VTYPE_DATETIME' ) )
    define( 'S2P_SDK_VTYPE_DATETIME', 5 );
if( !defined( 'S2P_SDK_VTYPE_ARRAY' ) )
    define( 'S2P_SDK_VTYPE_ARRAY', 6 );
if( !defined( 'S2P_SDK_VTYPE_BLARRAY' ) )
    define( 'S2P_SDK_VTYPE_BLARRAY', 7 );
if( !defined( 'S2P_SDK_VTYPE_BLOB' ) )
    define( 'S2P_SDK_VTYPE_BLOB', 8 );

class S2P_SDK_Scope_Variable extends S2P_SDK_Language
{
    // Tells if object structure should be walked and all subobjects properties set to null or onject should receive null value directly
    const NULL_FULL_OBJECT = true;

    const ERR_DEFINITION = 1, ERR_DEF_STRUCTURE = 2, ERR_SCOPE = 3, ERR_REGEXP = 4, ERR_PARSE = 5;

    /**
     * Blob or array structure definition
     * @var array $_definition
     */
    protected $_definition;
    /**
     * Value after validation
     * @var mixed $_value
     */
    protected $_value;

    const TYPE_STRING = S2P_SDK_VTYPE_STRING, TYPE_INT = S2P_SDK_VTYPE_INT, TYPE_FLOAT = S2P_SDK_VTYPE_FLOAT, TYPE_BOOL = S2P_SDK_VTYPE_BOOL, TYPE_DATETIME = S2P_SDK_VTYPE_DATETIME,
          TYPE_ARRAY = S2P_SDK_VTYPE_ARRAY, TYPE_BLOB_ARRAY = S2P_SDK_VTYPE_BLARRAY, TYPE_BLOB = S2P_SDK_VTYPE_BLOB;
    private static $TYPES_ARR = array(
        self::TYPE_STRING => array(
            'title' => 'string',
        ),
        self::TYPE_INT => array(
            'title' => 'int',
        ),
        self::TYPE_FLOAT => array(
            'title' => 'float',
        ),
        self::TYPE_BOOL => array(
            'title' => 'bool',
        ),
        self::TYPE_DATETIME => array(
            'title' => 'datetime',
        ),
        self::TYPE_ARRAY => array(
            'title' => 'array',
        ),
        self::TYPE_BLOB_ARRAY => array(
            'title' => 'array of objects',
        ),
        self::TYPE_BLOB => array(
            'title' => 'object',
        ),
    );

    function __construct( $definition = null )
    {
        parent::__construct();

        $this->reset_variable();

        if( !is_null( $definition ) )
            $this->structure_definition( $definition );
    }

    public function extract_values( $scope_arr, $params = false )
    {
        $this->reset_error();

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( !isset( $params['check_external_names'] ) )
            $params['check_external_names'] = true;

        if( !($variable_value = $this->extract_values_from_scope( $scope_arr, null, $params )) )
            return false;

        $this->_value = $variable_value;
        return $this->_value;
    }

    public function transform_keys( $scope_arr, $definition = null, $params = false )
    {
        if( empty( $scope_arr ) or !is_array( $scope_arr ) )
            return false;

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( !isset( $params['check_external_names'] ) )
            $params['check_external_names'] = true;
        if( empty( $params['parsing_path'] ) )
            $params['parsing_path'] = '';

        if( !empty( $params['check_external_names'] ) )
        {
            $scope_name_key = 'external_name';
            $output_name_key = 'name';
        } else
        {
            $scope_name_key = 'name';
            $output_name_key = 'external_name';
        }

        if( is_null( $definition ) )
            $definition = $this->_definition;

        if( empty( $definition ) or !self::valid_definition( $definition ) )
            return false;

        if( !array_key_exists( $definition[$scope_name_key], $scope_arr ) )
            return false;

        $current_value = array();
        if( self::scalar_type( $definition['type'] ) )
        {
            $current_value[ $definition[ $output_name_key ] ] = self::scalar_value( $definition['type'], $scope_arr[$definition[$scope_name_key]] );

        } else
        {
            $params['parsing_path'] .= ($params['parsing_path']!=''?'.':'').$definition[$scope_name_key];

            // Variable exists in scope
            if( empty( $scope_arr[$definition[$scope_name_key]] )
             or !is_array( $scope_arr[$definition[$scope_name_key]] )
             or !self::object_type( $definition['type'] ) )
                $current_value[ $definition[$output_name_key] ] = null;

            else
            {
                $current_value[$definition[$output_name_key]] = array();
                if( $definition['type'] == self::TYPE_BLOB )
                {
                    foreach( $definition['structure'] as $structure_element )
                    {
                        if( !self::valid_definition( $structure_element )
                         or ($property_result = $this->transform_keys( $scope_arr[$definition[$scope_name_key]], $structure_element, $params )) === false
                         or !is_array( $property_result ) )
                            continue;

                        $current_value[$definition[$output_name_key]] = array_merge( $current_value[$definition[$output_name_key]], $property_result );
                    }
                } elseif( $definition['type'] == self::TYPE_BLOB_ARRAY )
                {
                    $current_value[$definition[$output_name_key]] = array();
                    $knti = -1;
                    $initial_parsing_path = $params['parsing_path'];
                    foreach( $scope_arr[$definition[$scope_name_key]] as $element_scope )
                    {
                        $knti++;

                        if( !is_array( $element_scope ) )
                            continue;

                        $params['parsing_path'] = $initial_parsing_path.'['.$knti.']';

                        $node_arr = array();
                        foreach( $definition['structure'] as $structure_element )
                        {
                            if( !self::valid_definition( $structure_element )
                             or ($node_result = $this->transform_keys( $element_scope, $structure_element, $params )) === false
                             or !is_array( $node_result ) )
                                continue;

                            $node_arr = array_merge( $node_arr, $node_result );
                        }

                        if( !empty( $node_arr ) )
                            $current_value[$definition[$output_name_key]][] = $node_arr;
                    }

                    $params['parsing_path'] = $initial_parsing_path;
                }

                if( empty( $current_value[$definition[$output_name_key]] ) )
                    $current_value[$definition[$output_name_key]] = null;
            }
        }

        return $current_value;
    }

    protected function extract_values_from_scope( $scope_arr, $definition = null, $params = false )
    {
        if( empty( $scope_arr ) or !is_array( $scope_arr ) )
        {
            $this->set_error( self::ERR_SCOPE, self::s2p_t( 'Invalid scope.' ) );
            return false;
        }

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['nullify_full_object'] ) )
            $params['nullify_full_object'] = false;

        if( !empty( $params['nullify_full_object'] ) )
            $params['output_null_values'] = true;

        if( !isset( $params['check_external_names'] ) )
            $params['check_external_names'] = true;
        if( empty( $params['parsing_path'] ) )
            $params['parsing_path'] = '';
        if( !isset( $params['output_null_values'] ) )
            $params['output_null_values'] = true;
        if( empty( $params['skip_regexps'] ) )
            $params['skip_regexps'] = false;
        else
            $params['skip_regexps'] = (!empty( $params['skip_regexps'] )?true:false);

        if( !empty( $params['check_external_names'] ) )
        {
            $scope_name_key = 'external_name';
            $output_name_key = 'name';
        } else
        {
            $scope_name_key = 'name';
            $output_name_key = 'external_name';
        }

        if( is_null( $definition ) )
            $definition = $this->_definition;

        if( empty( $definition ) or !self::valid_definition( $definition ) )
        {
            $this->set_error( self::ERR_DEFINITION, self::s2p_t( 'Invalid definition for variable [%s]', (!empty( $definition['name'] )?$definition['name']:'???') ) );
            return false;
        }

        $current_value = array();
        if( (!empty( $params['nullify_full_object'] ) and self::scalar_type( $definition['type'] ))
         or !array_key_exists( $definition[$scope_name_key], $scope_arr ) )
        {
            if( ($null_value = $this->nullify( $definition, $params )) !== null
             or ($null_value === null and !empty( $params['output_null_values'] ))
             or empty( $params['parsing_path'] ) )
                $current_value[ $definition[ $output_name_key ] ] = $null_value;

        } else
        {
            $params['parsing_path'] .= ($params['parsing_path']!=''?'.':'').$definition[$scope_name_key];

            // Variable exists in scope
            if( self::scalar_type( $definition['type'] ) )
            {
                if( $scope_arr[$definition[$scope_name_key]] === null )
                    $var_val = null;
                else
                    $var_val = self::scalar_value( $definition['type'], $scope_arr[$definition[$scope_name_key]], $definition['array_type'], $definition['array_numeric_keys'] );

                if( is_scalar( $var_val )
                and (string)$var_val !== ''
                and empty( $params['skip_regexps'] )
                and !empty( $definition['regexp'] )
                and !preg_match( '/'.$definition['regexp'].'/', $var_val ) )
                {
                    $this->set_error( self::ERR_REGEXP,
                                        self::s2p_t( 'Variable [%s] is invalid.', (!empty( $params['parsing_path'] )?$params['parsing_path']:'???') ),
                                        sprintf( 'Variable [%s] failed regular expression [%s].',
                                            (!empty( $definition['display_name'] )?$definition['display_name']:'').
                                            (!empty( $params['parsing_path'] )?' - '.$params['parsing_path']:'???'),
                                            $definition['regexp'] ) );

                    return false;
                }

                $current_value[$definition[$output_name_key]] = $var_val;

            }

            else
            {
                if( empty( $scope_arr[$definition[$scope_name_key]] )
                 or !is_array( $scope_arr[$definition[$scope_name_key]] )
                 or !self::object_type( $definition['type'] ) )
                    $current_value[$definition[$output_name_key]] = null;

                else
                {
                    $current_value[$definition[$output_name_key]] = array();
                    if( $definition['type'] == self::TYPE_BLOB )
                    {
                        foreach( $definition['structure'] as $structure_element )
                        {
                            if( !self::valid_definition( $structure_element ) )
                                continue;

                            if( ($property_result = $this->extract_values_from_scope( $scope_arr[$definition[$scope_name_key]], $structure_element, $params )) === false
                             or !is_array( $property_result ) )
                            {
                                if( $this->has_error() )
                                    return false;

                                $this->set_error( self::ERR_PARSE, self::s2p_t( 'Error parsing variable [%s]', $params['parsing_path'] ) );
                                return false;
                            }

                            $current_value[$definition[$output_name_key]] = array_merge( $current_value[$definition[$output_name_key]], $property_result );
                        }
                    } elseif( $definition['type'] == self::TYPE_BLOB_ARRAY )
                    {
                        $current_value[$definition[$output_name_key]] = array();
                        $knti = -1;
                        $initial_parsing_path = $params['parsing_path'];
                        foreach( $scope_arr[$definition[$scope_name_key]] as $element_scope )
                        {
                            $knti++;

                            if( !is_array( $element_scope ) )
                                continue;

                            $params['parsing_path'] = $initial_parsing_path.'['.$knti.']';

                            $node_arr = array();
                            foreach( $definition['structure'] as $structure_element )
                            {
                                if( !self::valid_definition( $structure_element ) )
                                    continue;

                                if( ($node_result = $this->extract_values_from_scope( $element_scope, $structure_element, $params )) === false
                                 or !is_array( $node_result ) )
                                {
                                    // If we have an object in array which contains errors just pass on next one and set error...
                                    // If errors are not thrown you still can get a partial array with elements which pass validation
                                    $node_arr = null;
                                    break;
                                }

                                $node_arr = array_merge( $node_arr, $node_result );
                            }

                            if( !empty( $node_arr ) )
                                $current_value[$definition[$output_name_key]][] = $node_arr;
                        }

                        $params['parsing_path'] = $initial_parsing_path;
                    }

                    if( empty( $current_value[$definition[$output_name_key]] ) )
                        $current_value[$definition[$output_name_key]] = null;
                }
            }
        }

        return $current_value;
    }

    public function nullify( $definition = null, $params = false )
    {
        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( !isset( $params['check_external_names'] ) )
            $params['check_external_names'] = true;
        if( empty( $params['nullify_full_object'] ) )
            $params['nullify_full_object'] = false;

        if( !empty( $params['check_external_names'] ) )
            $output_name_key = 'name';
        else
            $output_name_key = 'external_name';

        $return_null_value = false;
        if( is_null( $definition ) )
            $definition = $this->_definition;

        else
        {
            // In case we should not nullify full object, give at leave first level of properties to null
            if( empty( $params['nullify_full_object'] )
            and !self::NULL_FULL_OBJECT )
                $return_null_value = true;
        }

        if( !empty( $return_null_value )
         or empty( $definition ) or !self::valid_definition( $definition )
         or empty( $definition['structure'] ) or !is_array( $definition['structure'] )
         or self::scalar_type( $definition['type'] )
         or (empty( $params['nullify_full_object'] ) and array_key_exists( 'default', $definition )) )
        {
            if( empty( $definition ) or !is_array( $definition ) )
                return null;

            if( !empty( $definition['check_constant'] ) and defined( $definition['check_constant'] )
            and constant( $definition['check_constant'] ) )
                return constant( $definition['check_constant'] );

            elseif( array_key_exists( 'default', $definition ) )
                return $definition['default'];

            return null;
        }

        $null_arr = array();
        switch( $definition['type'] )
        {
            case self::TYPE_BLOB_ARRAY:
                $node_arr = array();
                foreach( $definition['structure'] as $element_definition )
                {
                    if( !self::valid_definition( $element_definition ) )
                        continue;

                    $node_arr[$element_definition[$output_name_key]] = $this->nullify( $element_definition, $params );
                }
                $null_arr[] = $node_arr;
            break;
            case self::TYPE_BLOB:
                foreach( $definition['structure'] as $element_definition )
                {
                    if( !self::valid_definition( $element_definition ) )
                        continue;

                    $null_arr[$element_definition[$output_name_key]] = $this->nullify( $element_definition, $params );
                }
            break;
        }

        return $null_arr;
    }

    public static function scalar_value( $var_type, $value, $array_type = false, $array_numeric_keys = false )
    {
        if( !self::scalar_type( $var_type ) )
            return null;

        $result = null;
        switch( $var_type )
        {
            case self::TYPE_STRING:
                if( is_scalar( $value ) )
                    $result = (string)$value;
            break;

            case self::TYPE_INT:
                if( is_scalar( $value ) )
                    // workaround for float values converted in int (they might loose precision)
                    $result = intval( number_format( $value, 0, '.', '' ) );
            break;

            case self::TYPE_FLOAT:
                if( is_scalar( $value ) )
                    $result = floatval( $value );
            break;

            case self::TYPE_BOOL:
                $result = (empty( $value )?false:true);
            break;

            case self::TYPE_DATETIME:
                $value = trim( $value );
                if( !empty( $value )
                and strlen( $value ) == 14 )
                {
                    $year = (int)@substr( $value, 0, 4 );
                    $month = (int)@substr( $value, 4, 2 );
                    $day = (int)@substr( $value, 6, 2 );
                    $hour = (int)@substr( $value, 8, 2 );
                    $minute = (int)@substr( $value, 10, 2 );
                    $second = (int)@substr( $value, 12, 2 );

                    // get a good year margin...
                    if( $year > 1000 and $year < 10000
                    and $month >= 1 and $month <= 12
                    and $day >= 1 and $day <= 31
                    and $hour >= 0 and $hour < 24
                    and $minute >= 0 and $minute < 60
                    and $second >= 0 and $second < 60 )
                        $result = $year.
                                  ($month<10?'0':'').$month.
                                  ($day<10?'0':'').$day.
                                  ($hour<10?'0':'').$hour.
                                  ($minute<10?'0':'').$minute.
                                  ($second<10?'0':'').$second;
                }
            break;

            case self::TYPE_ARRAY:
                $result = array();
                if( !empty( $value ) and is_array( $value ) )
                {
                    foreach( $value as $key => $val )
                    {
                        if( !is_scalar( $val ) )
                            continue;

                        if( !empty( $array_type ) and self::scalar_type( $array_type ) )
                            $key_val = self::scalar_value( $array_type, $val );
                        else
                            $key_val = $val;

                        if( !empty( $array_numeric_keys ) )
                            $result[] = $key_val;
                        else
                            $result[$key] = $key_val;
                    }
                }
            break;
        }

        return $result;
    }

    public function reset_variable()
    {
        $this->_value = null;
        $this->_definition = null;
    }

    public function structure_definition( $definition = null )
    {
        if( is_null( $definition ) )
            return $this->_definition;

        if( !($definition = self::validate_definition( $definition )) )
        {
            // Error is set statically in validate_definition() call
            $this->copy_static_error();
            return false;
        }

        $this->_definition = $definition;
        return $this->_definition;
    }

    public function value()
    {
        return $this->_value;
    }

    public static function scalar_type( $type )
    {
        $type = intval( $type );
        return (in_array( $type, array( self::TYPE_STRING, self::TYPE_INT, self::TYPE_FLOAT, self::TYPE_BOOL, self::TYPE_DATETIME, self::TYPE_ARRAY ) )?true:false);
    }

    public static function object_type( $type )
    {
        $type = intval( $type );
        return (in_array( $type, array( self::TYPE_BLOB_ARRAY, self::TYPE_BLOB ) )?true:false);
    }

    public static function get_types()
    {
        return self::$TYPES_ARR;
    }

    public static function valid_type( $type )
    {
        $type = intval( $type );
        if( empty( $type )
         or !($types_arr = self::get_types()) or empty( $types_arr[$type] ) )
            return false;

        return $types_arr[$type];
    }

    public static function default_definition_fields()
    {
        return array(
            'name' => '',
            'external_name' => '',
            'display_name' => '', // a nice name to display to end user
            'type' => 0,
            'array_type' => 0,
            'array_numeric_keys' => true,
            'default' => null,
            'regexp' => '',
            'structure' => null,
            'value_source' => 0,
            'check_constant' => '',
            'hint_path' => '',
        );
    }

    public static function valid_definition( $definition_arr )
    {
        if( empty( $definition_arr ) or !is_array( $definition_arr )
         or empty( $definition_arr['name'] ) or empty( $definition_arr['external_name'] )
         or empty( $definition_arr['type'] ) or !self::valid_type( $definition_arr['type'] )
         or !array_key_exists( 'structure', $definition_arr ) )
            return false;

        return true;
    }

    public static function validate_definition( $definition_arr, $params = false )
    {
        static $default_definition = null;

        if( is_null( $default_definition ) )
        {
            // First level call
            $default_definition = self::default_definition_fields();
            self::st_reset_error();
        }

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['path'] ) )
            $params['path'] = '';

        if( empty( $definition_arr ) or !is_array( $definition_arr ) )
            return $default_definition;

        $new_definition_arr = array();
        foreach( $default_definition as $key => $def_value )
        {
            if( !array_key_exists( $key, $definition_arr ) )
            {
                if( $key == 'default' )
                    continue;

                $new_definition_arr[$key] = $def_value;
            } else
                $new_definition_arr[$key] = $definition_arr[$key];
        }

        if( empty( $new_definition_arr['name'] ) and !empty( $new_definition_arr['external_name'] ) )
            $new_definition_arr['name'] = $new_definition_arr['external_name'];

        $params['path'] .= (!empty( $params['path'] )?'.':'').$new_definition_arr['name'];

        $new_definition_arr['hint_path'] = $params['path'];

        if( !self::valid_definition( $new_definition_arr ) )
        {
            self::st_set_error( self::ERR_DEFINITION,
                                    self::s2p_t( 'Invalid definition for variable [%s]', (!empty( $params['path'] )?$params['path']:'???') )
                            );
            return null;
        }

        if( self::object_type( $new_definition_arr['type'] )
        and (empty( $new_definition_arr['structure'] ) or !is_array( $new_definition_arr['structure'] )) )
        {
            self::st_set_error( self::ERR_DEF_STRUCTURE,
                                    self::s2p_t( 'Blobs and blob arrays should have a defined structure [%s]', (!empty( $params['path'] )?$params['path']:'???') )
                            );
            return null;
        }

        if( !empty( $new_definition_arr['structure'] ) and is_array( $new_definition_arr['structure'] ) )
        {
            foreach( $new_definition_arr['structure'] as $key => $element )
            {
                $new_definition_arr['structure'][$key] = self::validate_definition( $element, $params );

                if( self::st_has_error() )
                    return null;
            }
        }

        return $new_definition_arr;
    }

}
