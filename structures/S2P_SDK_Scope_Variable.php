<?php
namespace S2P_SDK;

if (!defined('S2P_SDK_VTYPE_STRING')) {
    define('S2P_SDK_VTYPE_STRING', 1);
}
if (!defined('S2P_SDK_VTYPE_INT')) {
    define('S2P_SDK_VTYPE_INT', 2);
}
if (!defined('S2P_SDK_VTYPE_FLOAT')) {
    define('S2P_SDK_VTYPE_FLOAT', 3);
}
if (!defined('S2P_SDK_VTYPE_BOOL')) {
    define('S2P_SDK_VTYPE_BOOL', 4);
}
if (!defined('S2P_SDK_VTYPE_DATETIME')) {
    define('S2P_SDK_VTYPE_DATETIME', 5);
}
if (!defined('S2P_SDK_VTYPE_ARRAY')) {
    define('S2P_SDK_VTYPE_ARRAY', 6);
}
if (!defined('S2P_SDK_VTYPE_BLARRAY')) {
    define('S2P_SDK_VTYPE_BLARRAY', 7);
}
if (!defined('S2P_SDK_VTYPE_BLOB')) {
    define('S2P_SDK_VTYPE_BLOB', 8);
}
if (!defined('S2P_SDK_VTYPE_BLOB_GROUP')) {
    define('S2P_SDK_VTYPE_BLOB_GROUP', 9);
}
if (!defined('S2P_SDK_VTYPE_DATE')) {
    define('S2P_SDK_VTYPE_DATE', 10);
}
if (!defined('S2P_SDK_VTYPE_LONG')) {
    define('S2P_SDK_VTYPE_LONG', 11);
}

class S2P_SDK_Scope_Variable extends S2P_SDK_Language
{
    // Tells if object structure should be walked and all subobjects properties set to null or onject should receive null value directly
    public const NULL_FULL_OBJECT = true;

    public const ERR_DEFINITION = 1, ERR_DEF_STRUCTURE = 2, ERR_SCOPE = 3, ERR_REGEXP = 4, ERR_PARSE = 5;

    public const TYPE_STRING = S2P_SDK_VTYPE_STRING, TYPE_INT = S2P_SDK_VTYPE_INT, TYPE_FLOAT = S2P_SDK_VTYPE_FLOAT, TYPE_BOOL = S2P_SDK_VTYPE_BOOL,
        TYPE_DATETIME = S2P_SDK_VTYPE_DATETIME, TYPE_ARRAY = S2P_SDK_VTYPE_ARRAY, TYPE_BLOB_ARRAY = S2P_SDK_VTYPE_BLARRAY,
        TYPE_BLOB = S2P_SDK_VTYPE_BLOB, TYPE_BLOB_GROUP = S2P_SDK_VTYPE_BLOB_GROUP, TYPE_DATE = S2P_SDK_VTYPE_DATE,
        TYPE_LONG = S2P_SDK_VTYPE_LONG;

    /**
     * Blob or array structure definition
     * @var array
     */
    protected $_definition;

    /**
     * Value after validation
     * @var mixed
     */
    protected $_value;

    private static $TYPES_ARR = [
        self::TYPE_STRING => [
            'title' => 'string',
        ],
        self::TYPE_INT => [
            'title' => 'int',
        ],
        self::TYPE_FLOAT => [
            'title' => 'float',
        ],
        self::TYPE_BOOL => [
            'title' => 'bool',
        ],
        self::TYPE_DATETIME => [
            'title' => 'datetime',
        ],
        self::TYPE_ARRAY => [
            'title' => 'array',
        ],
        self::TYPE_BLOB_ARRAY => [
            'title' => 'array of objects',
        ],
        self::TYPE_BLOB => [
            'title' => 'object',
        ],
        self::TYPE_BLOB_GROUP => [
            'title' => 'object group',
        ],
        self::TYPE_DATE => [
            'title' => 'date',
        ],
        self::TYPE_LONG => [
            'title' => 'long',
        ],
    ];

    public function __construct($definition = null)
    {
        parent::__construct();

        $this->reset_variable();

        if (null !== $definition) {
            $this->structure_definition($definition);
        }
    }

    public function extract_values(array $scope_arr, array $params = []) : ?array
    {
        $this->reset_error();

        if (!isset($params['check_external_names'])) {
            $params['check_external_names'] = true;
        }

        if (!($variable_value = $this->extract_values_from_scope($scope_arr, null, $params))) {
            return null;
        }

        $this->_value = $variable_value;

        return $this->_value;
    }

    public function transform_keys($scope_arr, $definition = null, array $params = [])
    {
        if (empty($scope_arr) || !is_array($scope_arr)) {
            return false;
        }

        if (!isset($params['check_external_names'])) {
            $params['check_external_names'] = true;
        }
        if (empty($params['parsing_path'])) {
            $params['parsing_path'] = '';
        }

        if (!empty($params['check_external_names'])) {
            $scope_name_key = 'external_name';
            $output_name_key = 'name';
        } else {
            $scope_name_key = 'name';
            $output_name_key = 'external_name';
        }

        if (null === $definition) {
            $definition = $this->_definition;
        }

        if (empty($definition) || !self::valid_definition($definition)) {
            return false;
        }

        if ($definition['type'] !== self::TYPE_BLOB_GROUP
        && !array_key_exists($definition[$scope_name_key], $scope_arr)) {
            return false;
        }

        $current_value = [];
        if (self::scalar_type($definition['type'])) {
            $current_value[$definition[$output_name_key]] = self::scalar_value($definition['type'], $scope_arr[$definition[$scope_name_key]]);
        } elseif ($definition['type'] === self::TYPE_BLOB_GROUP) {
            foreach ($definition['structure'] as $structure_element) {
                if (!self::valid_definition($structure_element)
                 || ($property_result = $this->transform_keys($scope_arr, $structure_element, $params)) === false
                 || !is_array($property_result)) {
                    continue;
                }

                $current_value = array_merge($current_value, $property_result);
            }
        } else {
            $params['parsing_path'] .= ($params['parsing_path'] !== '' ? '.' : '').$definition[$scope_name_key];

            // Variable exists in scope
            if (empty($scope_arr[$definition[$scope_name_key]])
             || !is_array($scope_arr[$definition[$scope_name_key]])
             || !self::object_type($definition['type'])) {
                $current_value[$definition[$output_name_key]] = null;
            } else {
                $current_value[$definition[$output_name_key]] = [];
                if ($definition['type'] == self::TYPE_BLOB) {
                    foreach ($definition['structure'] as $structure_element) {
                        if (!self::valid_definition($structure_element)
                         || ($property_result = $this->transform_keys($scope_arr[$definition[$scope_name_key]], $structure_element, $params)) === false
                         || !is_array($property_result)) {
                            continue;
                        }

                        $current_value[$definition[$output_name_key]] = array_merge($current_value[$definition[$output_name_key]], $property_result);
                    }
                } elseif ($definition['type'] == self::TYPE_BLOB_ARRAY) {
                    $current_value[$definition[$output_name_key]] = [];
                    $knti = -1;
                    $initial_parsing_path = $params['parsing_path'];
                    foreach ($scope_arr[$definition[$scope_name_key]] as $element_scope) {
                        $knti++;

                        if (!is_array($element_scope)) {
                            continue;
                        }

                        $params['parsing_path'] = $initial_parsing_path.'['.$knti.']';

                        $node_arr = [];
                        foreach ($definition['structure'] as $structure_element) {
                            if (!self::valid_definition($structure_element)
                             || ($node_result = $this->transform_keys($element_scope, $structure_element, $params)) === false
                             || !is_array($node_result)) {
                                continue;
                            }

                            $node_arr = array_merge($node_arr, $node_result);
                        }

                        if (!empty($node_arr)) {
                            $current_value[$definition[$output_name_key]][] = $node_arr;
                        }
                    }

                    $params['parsing_path'] = $initial_parsing_path;
                }

                if (empty($current_value[$definition[$output_name_key]])) {
                    $current_value[$definition[$output_name_key]] = null;
                }
            }
        }

        return $current_value;
    }

    public function nullify(?array $definition = null, array $params = []) : ?array
    {
        if (!isset($params['check_external_names'])) {
            $params['check_external_names'] = true;
        }
        if (empty($params['nullify_full_object'])) {
            $params['nullify_full_object'] = false;
        }

        $output_name_key = !empty($params['check_external_names'])
            ? 'name'
            : 'external_name';

        $return_null_value = false;
        if (null === $definition) {
            $definition = $this->_definition;
        } elseif (empty($params['nullify_full_object'])
              && !self::NULL_FULL_OBJECT) {
            // In case we should not nullify full object, give at leave first level of properties to null
            $return_null_value = true;
        }

        if ($return_null_value
         || !$definition || !self::valid_definition($definition)
         || empty($definition['structure']) || !is_array($definition['structure'])
         || self::scalar_type($definition['type'])
         || (empty($params['nullify_full_object']) && array_key_exists('default', $definition))) {
            if (empty($definition) || !is_array($definition)) {
                return null;
            }

            if (!empty($definition['check_constant']) && defined($definition['check_constant'])
                && constant($definition['check_constant'])) {
                return constant($definition['check_constant']);
            }

            if (array_key_exists('default', $definition)) {
                return $definition['default'];
            }

            return null;
        }

        $null_arr = [];

        switch ($definition['type']) {
            case self::TYPE_BLOB_ARRAY:
                $node_arr = [];
                foreach ($definition['structure'] as $element_definition) {
                    if (!self::valid_definition($element_definition)) {
                        continue;
                    }

                    $node_arr[$element_definition[$output_name_key]] = $this->nullify($element_definition, $params);
                }
                $null_arr[] = $node_arr;
                break;

            case self::TYPE_BLOB:
            case self::TYPE_BLOB_GROUP:
                foreach ($definition['structure'] as $element_definition) {
                    if (!self::valid_definition($element_definition)) {
                        continue;
                    }

                    $null_arr[$element_definition[$output_name_key]] = $this->nullify($element_definition, $params);
                }
                break;
        }

        return $null_arr;
    }

    public function reset_variable()
    {
        $this->_value = null;
        $this->_definition = null;
    }

    public function structure_definition($definition = null)
    {
        if ($definition === null) {
            return $this->_definition;
        }

        if (!($definition = self::validate_definition($definition))) {
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

    /**
     * @param array $scope_arr
     * @param null|array $definition
     * @param array|false $params
     *
     * @return array|false
     */
    protected function extract_values_from_scope(array $scope_arr, ?array $definition = null, array $params = [])
    {
        if (empty($params['parsing_path'])) {
            $params['parsing_path'] = '';
        }

        $params['nullify_full_object'] = (!empty($params['nullify_full_object']));

        if (!empty($params['nullify_full_object'])) {
            $params['output_null_values'] = true;
        }

        if (!isset($params['check_external_names'])) {
            $params['check_external_names'] = true;
        } else {
            $params['check_external_names'] = (!empty($params['check_external_names']));
        }

        if (!isset($params['output_null_values'])) {
            $params['output_null_values'] = true;
        } else {
            $params['output_null_values'] = (!empty($params['output_null_values']));
        }

        $params['skip_regexps'] = (!empty($params['skip_regexps']));

        if (!empty($params['check_external_names'])) {
            $scope_name_key = 'external_name';
            $output_name_key = 'name';
        } else {
            $scope_name_key = 'name';
            $output_name_key = 'external_name';
        }

        if (null === $definition) {
            $definition = $this->_definition;
        }

        if (!$definition || !self::valid_definition($definition)) {
            $this->set_error(self::ERR_DEFINITION,
                self::s2p_t('Invalid definition for variable [%s]', ($definition['name'] ?? '') ?: '???')
            );

            return false;
        }

        $definition['type'] = (int)$definition['type'];

        $key_exists_is_scope = array_key_exists($definition[$scope_name_key], $scope_arr);

        $current_value = [];
        if ($definition['type'] !== self::TYPE_BLOB_GROUP
         && (
             (!empty($params['nullify_full_object']) && self::scalar_type($definition['type']))
             || !$key_exists_is_scope
         )) {
            if (empty($definition['ignore_if_not_in_scope'])
             || $key_exists_is_scope) {
                if (($null_value = $this->nullify($definition, $params)) !== null
                     || ($null_value === null && !empty($params['output_null_values']))
                     || empty($params['parsing_path'])
                ) {
                    $assign_value = true;
                    if (!empty($definition['skip_if_default'])
                     && array_key_exists('default', $definition)
                     && $null_value === $definition['default']) {
                        $assign_value = false;
                    }

                    if ($assign_value) {
                        $current_value[$definition[$output_name_key]] = $null_value;
                    }
                }
            }
        } elseif ($definition['type'] === self::TYPE_BLOB_GROUP) {
            if ($key_exists_is_scope
             || empty($definition['ignore_if_not_in_scope'])) {
                foreach ($definition['structure'] as $structure_element) {
                    if (!self::valid_definition($structure_element)) {
                        continue;
                    }

                    if (($property_result = $this->extract_values_from_scope($scope_arr, $structure_element, $params)) === false
                     || !is_array($property_result)) {
                        if ($this->has_error()) {
                            return false;
                        }

                        $this->set_error(self::ERR_PARSE, self::s2p_t('Error parsing variable [%s]', $params['parsing_path']));

                        return false;
                    }

                    $current_value = array_merge($current_value, $property_result);
                }
            }
        } else {
            $params['parsing_path'] .= ($params['parsing_path'] !== '' ? '.' : '').$definition[$scope_name_key];

            if ($key_exists_is_scope
             || empty($definition['ignore_if_not_in_scope'])) {
                // Variable exists in scope
                if (self::scalar_type($definition['type'])) {
                    if ($scope_arr[$definition[$scope_name_key]] === null) {
                        $var_val = null;
                    } else {
                        $var_val = self::scalar_value($definition['type'], $scope_arr[$definition[$scope_name_key]], $definition['array_type'], $definition['array_numeric_keys']);
                    }

                    if (is_scalar($var_val)
                     && (string)$var_val !== ''
                     && empty($params['skip_regexps'])
                     && !empty($definition['regexp'])
                     && !preg_match('/'.$definition['regexp'].'/', $var_val)) {
                        $this->set_error(self::ERR_REGEXP,
                            self::s2p_t('Variable [%s] is invalid.', (!empty($params['parsing_path']) ? $params['parsing_path'] : '???')),
                            sprintf('Variable [%s] failed regular expression [%s].',
                                (!empty($definition['display_name']) ? $definition['display_name'] : '')
                                .(!empty($params['parsing_path']) ? ' - '.$params['parsing_path'] : '???'),
                                $definition['regexp']));

                        return false;
                    }

                    $assign_value = true;
                    if (!empty($definition['skip_if_default'])
                    && array_key_exists('default', $definition)
                    && $var_val === $definition['default']) {
                        $assign_value = false;
                    }

                    if ($assign_value) {
                        $current_value[$definition[$output_name_key]] = $var_val;
                    }
                } else {
                    if (empty($scope_arr[$definition[$scope_name_key]])
                     || !is_array($scope_arr[$definition[$scope_name_key]])
                     || !self::object_type($definition['type'])) {
                        $current_value[$definition[$output_name_key]] = null;
                    } else {
                        $current_value[$definition[$output_name_key]] = [];
                        if ($definition['type'] === self::TYPE_BLOB) {
                            foreach ($definition['structure'] as $structure_element) {
                                if (!self::valid_definition($structure_element)) {
                                    continue;
                                }

                                if (($property_result = $this->extract_values_from_scope($scope_arr[$definition[$scope_name_key]], $structure_element, $params)) === false
                                 || !is_array($property_result)) {
                                    if ($this->has_error()) {
                                        return false;
                                    }

                                    $this->set_error(self::ERR_PARSE, self::s2p_t('Error parsing variable [%s]', $params['parsing_path']));

                                    return false;
                                }

                                $current_value[$definition[$output_name_key]] = array_merge($current_value[$definition[$output_name_key]], $property_result);
                            }
                        } elseif ($definition['type'] == self::TYPE_BLOB_ARRAY) {
                            $current_value[$definition[$output_name_key]] = [];
                            $knti = -1;
                            $initial_parsing_path = $params['parsing_path'];
                            foreach ($scope_arr[$definition[$scope_name_key]] as $element_scope) {
                                $knti++;

                                if (!is_array($element_scope)) {
                                    continue;
                                }

                                $params['parsing_path'] = $initial_parsing_path.'['.$knti.']';

                                $node_arr = [];
                                foreach ($definition['structure'] as $structure_element) {
                                    if (!self::valid_definition($structure_element)) {
                                        continue;
                                    }

                                    if (($node_result = $this->extract_values_from_scope($element_scope, $structure_element, $params)) === false
                                     || !is_array($node_result)) {
                                        // If we have an object in array which contains errors just pass on next one and set error...
                                        // If errors are not thrown you still can get a partial array with elements which pass validation
                                        $node_arr = null;
                                        break;
                                    }

                                    $node_arr = array_merge($node_arr, $node_result);
                                }

                                if (!empty($node_arr)) {
                                    $current_value[$definition[$output_name_key]][] = $node_arr;
                                }
                            }

                            $params['parsing_path'] = $initial_parsing_path;
                        }

                        if (empty($current_value[$definition[$output_name_key]])) {
                            $current_value[$definition[$output_name_key]] = null;
                        }
                    }
                }
            }
        }

        return $current_value;
    }

    public static function scalar_value(int $var_type, $value, int $array_type = 0, bool $array_numeric_keys = false)
    {
        if (!self::scalar_type($var_type)) {
            return null;
        }

        $result = null;

        switch ($var_type) {
            case self::TYPE_STRING:
                if (is_scalar($value)) {
                    $result = (string)$value;
                }
                break;

            case self::TYPE_INT:
                if (is_scalar($value)) {
                    // workaround for float values converted in int (they might lose precision)
                    $result = (int)number_format((float)$value, 0, '.', '');
                }
                break;

            case self::TYPE_LONG:
                if (is_scalar($value)) {
                    $result = preg_replace('/[^0-9]/', '', $value);
                }
                break;

            case self::TYPE_FLOAT:
                if (is_scalar($value)) {
                    $result = (float)$value;
                }
                break;

            case self::TYPE_BOOL:
                if (is_string($value)) {
                    if ($value === 'true') {
                        $result = true;
                    } elseif ($value === 'false') {
                        $result = false;
                    } elseif ($value === 'null') {
                        $result = null;
                    }
                } else {
                    $result = empty($value);
                }
                break;

            case self::TYPE_DATETIME:
                $value = trim($value);
                if (!empty($value)
                && strlen($value) === 14) {
                    $year = (int)@substr($value, 0, 4);
                    $month = (int)@substr($value, 4, 2);
                    $day = (int)@substr($value, 6, 2);
                    $hour = (int)@substr($value, 8, 2);
                    $minute = (int)@substr($value, 10, 2);
                    $second = (int)@substr($value, 12, 2);

                    // get a good year margin...
                    if ($year > 1000 && $year < 10000
                    && $month >= 1 && $month <= 12
                    && $day >= 1 && $day <= 31
                    && $hour >= 0 && $hour < 24
                    && $minute >= 0 && $minute < 60
                    && $second >= 0 && $second < 60) {
                        $result = $year
                                  .($month < 10 ? '0' : '').$month
                                  .($day < 10 ? '0' : '').$day
                                  .($hour < 10 ? '0' : '').$hour
                                  .($minute < 10 ? '0' : '').$minute
                                  .($second < 10 ? '0' : '').$second;
                    }
                }
                break;

            case self::TYPE_DATE:
                $value = trim($value);
                if (!empty($value)
                    && strlen($value) === 8) {
                    $year = (int)@substr($value, 0, 4);
                    $month = (int)@substr($value, 4, 2);
                    $day = (int)@substr($value, 6, 2);

                    // get a good year margin...
                    if ($year > 1000 && $year < 10000
                    && $month >= 1 && $month <= 12
                    && $day >= 1 && $day <= 31) {
                        $result = $year
                                  .($month < 10 ? '0' : '').$month
                                  .($day < 10 ? '0' : '').$day;
                    }
                }
                break;

            case self::TYPE_ARRAY:
                $result = [];
                if (!empty($value) && is_array($value)) {
                    foreach ($value as $key => $val) {
                        if (!is_scalar($val)) {
                            continue;
                        }

                        if (!empty($array_type) && self::scalar_type($array_type)) {
                            $key_val = self::scalar_value($array_type, $val);
                        } else {
                            $key_val = $val;
                        }

                        if ($array_numeric_keys) {
                            $result[] = $key_val;
                        } else {
                            $result[$key] = $key_val;
                        }
                    }
                }
                break;
        }

        return $result;
    }

    public static function scalar_type(int $type) : bool
    {
        return in_array($type,
            [self::TYPE_STRING, self::TYPE_INT, self::TYPE_FLOAT, self::TYPE_BOOL,
                self::TYPE_DATETIME, self::TYPE_DATE, self::TYPE_ARRAY, self::TYPE_LONG], true);
    }

    public static function object_type(int $type) : bool
    {
        return in_array($type, [self::TYPE_BLOB_ARRAY, self::TYPE_BLOB, self::TYPE_BLOB_GROUP], true);
    }

    public static function get_types() : array
    {
        return self::$TYPES_ARR;
    }

    public static function valid_type(int $type) : ?array
    {
        if (!$type
            || !($types_arr = self::get_types())) {
            return null;
        }

        return $types_arr[$type] ?? null;
    }

    public static function default_definition_fields() : array
    {
        return [
            'name'                   => '',
            'external_name'          => '',
            'display_name'           => '', // a nice name to display to end user
            'type'                   => 0,
            'array_type'             => 0,
            'array_numeric_keys'     => true,
            'default'                => null,
            'skip_if_default'        => false,
            'regexp'                 => '',
            'structure'              => null,
            'value_source'           => 0,
            'check_constant'         => '',
            'hint_path'              => '',
            'ignore_if_not_in_scope' => false,
        ];
    }

    public static function valid_definition(array $definition_arr) : bool
    {
        return $definition_arr
               && !empty($definition_arr['type']) && self::valid_type($definition_arr['type'])
               && ((!empty($definition_arr['name']) && !empty($definition_arr['external_name']))
                   || (int)$definition_arr['type'] === self::TYPE_BLOB_GROUP
               )
               && array_key_exists('structure', $definition_arr);
    }

    public static function validate_definition(array $definition_arr, array $params = []) : ?array
    {
        static $default_definition = null;

        if ($default_definition === null) {
            // First level call
            $default_definition = self::default_definition_fields();
            self::st_reset_error();
        }

        if (empty($params['path'])) {
            $params['path'] = '';
        }

        if (!$definition_arr) {
            return $default_definition;
        }

        $new_definition_arr = [];
        foreach ($default_definition as $key => $def_value) {
            if (!array_key_exists($key, $definition_arr)) {
                if ($key === 'default') {
                    continue;
                }

                $new_definition_arr[$key] = $def_value;
            } else {
                $new_definition_arr[$key] = $definition_arr[$key];
            }
        }

        if (empty($new_definition_arr['name']) && !empty($new_definition_arr['external_name'])) {
            $new_definition_arr['name'] = $new_definition_arr['external_name'];
        }

        if ($new_definition_arr['type'] !== self::TYPE_BLOB_GROUP) {
            $params['path'] .= (!empty($params['path']) ? '.' : '').$new_definition_arr['name'];
        }

        $new_definition_arr['hint_path'] = $params['path'];

        if (!self::valid_definition($new_definition_arr)) {
            self::st_set_error(self::ERR_DEFINITION,
                self::s2p_t('Invalid definition for variable [%s]', (!empty($params['path']) ? $params['path'] : '???'))
            );

            return null;
        }

        if (self::object_type($new_definition_arr['type'])
        && (empty($new_definition_arr['structure']) || !is_array($new_definition_arr['structure']))) {
            self::st_set_error(self::ERR_DEF_STRUCTURE,
                self::s2p_t('Blobs and blob arrays should have a defined structure [%s]', (!empty($params['path']) ? $params['path'] : '???'))
            );

            return null;
        }

        if (!empty($new_definition_arr['structure']) && is_array($new_definition_arr['structure'])) {
            foreach ($new_definition_arr['structure'] as $key => $element) {
                $new_definition_arr['structure'][$key] = self::validate_definition($element, $params);

                if (self::st_has_error()) {
                    return null;
                }
            }
        }

        return $new_definition_arr;
    }
}
