<?php
namespace S2P_SDK;

abstract class S2P_SDK_Scope_Structure extends S2P_SDK_Language
{
    public const ERR_JSON = 1, ERR_VARIABLE = 2, ERR_DEFINITION = 3, ERR_MERGE = 4;

    /** @var null|S2P_SDK_Scope_Variable */
    protected $_var = null;

    /**
     * Structures can be merged to parse responses containing more defined structures or to create requests containing
     * multiple structures Array of S2P_SDK_Scope_Structure objects
     *
     * @var null|array
     */
    protected $_merged_structures;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Function should return array with full variable definition
     * @return array
     */
    abstract public function get_definition() : array;

    /**
     * Function should return structure definition for blobs or array variables
     * @return array
     */
    abstract public function get_structure_definition() : array;

    /**
     * Overwrite this method in case you want to add keys in "root" of the structure
     * @return array
     */
    public function get_merged_structure_definition() : array
    {
        return [];
    }

    public function get_validated_definition() : ?array
    {
        if (!($definition_arr = $this->get_definition())) {
            $this->set_error(self::ERR_DEFINITION, self::s2p_t('Invalid definition.'));

            return null;
        }

        if (!($definition_arr = S2P_SDK_Scope_Variable::validate_definition($definition_arr))) {
            $this->copy_static_error();

            return null;
        }

        return $definition_arr;
    }

    /**
     * Returns structure internal name or false on error.
     *
     * @return null|string Structure internal name
     */
    public function get_name() : ?string
    {
        if (!($definition_arr = $this->get_validated_definition())) {
            return null;
        }

        return $definition_arr['name'];
    }

    /**
     * Returns structure internal name or false on error.
     *
     * @return null|string Structure internal name
     */
    public function get_external_name() : ?string
    {
        if (!($definition_arr = $this->get_validated_definition())) {
            return null;
        }

        return $definition_arr['external_name'];
    }

    /**
     * Returns validated definition with variable names as keys
     *
     * @param null|array $definition_arr
     * @param bool $top_level
     * @return null|array|bool Structure definition with variable names as keys
     */
    public function get_structure_with_keys(?array $definition_arr = null, bool $top_level = true)
    {
        static $result_definition = null;

        if ($result_definition !== null) {
            return $result_definition;
        }

        if ($definition_arr === null
        && !($definition_arr = $this->get_validated_definition())) {
            return false;
        }

        if (empty($definition_arr) || !is_array($definition_arr)) {
            return null;
        }

        $new_definition = [];
        if (!empty($definition_arr['structure']) && is_array($definition_arr['structure'])) {
            foreach ($definition_arr['structure'] as $element_definition) {
                if ($definition_arr['type'] === S2P_SDK_Scope_Variable::TYPE_BLOB_GROUP) {
                    $new_definition = array_merge($new_definition, $this->get_structure_with_keys($element_definition, false));
                } else {
                    if (empty($new_definition[$definition_arr['name']])) {
                        $new_definition[$definition_arr['name']] = [];
                    }

                    $new_definition[$definition_arr['name']] = array_merge($new_definition[$definition_arr['name']], $this->get_structure_with_keys($element_definition, false));
                }
            }
        } else {
            $new_definition[$definition_arr['name']] = $definition_arr;
        }

        if ($top_level) {
            $result_definition = $new_definition;
        }

        return $new_definition;
    }

    /**
     * Returns validated definition with variable external names as keys
     *
     * @param null|array $definition_arr
     * @param bool $top_level
     * @return null|bool|array Structure definition with variable external names as keys
     */
    public function get_structure_with_external_keys(?array $definition_arr = null, bool $top_level = true)
    {
        static $result_definition_ext = null;

        if ($result_definition_ext !== null) {
            return $result_definition_ext;
        }

        if ($definition_arr === null
        && !($definition_arr = $this->get_validated_definition())) {
            return false;
        }

        if (empty($definition_arr) || !is_array($definition_arr)) {
            return null;
        }

        $new_definition = [];
        if (!empty($definition_arr['structure']) && is_array($definition_arr['structure'])) {
            foreach ($definition_arr['structure'] as $element_definition) {
                if ($definition_arr['type'] === S2P_SDK_Scope_Variable::TYPE_BLOB_GROUP) {
                    $new_definition = array_merge($new_definition, $this->get_structure_with_external_keys($element_definition, false));
                } else {
                    if (empty($new_definition[$definition_arr['external_name']])) {
                        $new_definition[$definition_arr['external_name']] = [];
                    }

                    $new_definition[$definition_arr['external_name']] = array_merge($new_definition[$definition_arr['external_name']], $this->get_structure_with_external_keys($element_definition, false));
                }
            }
        } else {
            $new_definition[$definition_arr['external_name']] = $definition_arr;
        }

        if ($top_level) {
            $result_definition_ext = $new_definition;
        }

        return $new_definition;
    }

    /**
     * Merge a structure with current one to form a request or to parse a response
     * Order of merged structures will determine order of keys in resulting JSON
     *
     * @param S2P_SDK_Scope_Structure $structure
     * @param bool $for_request
     *
     * @return bool
     */
    public function merge_structure(?self $structure, bool $for_request = false)
    {
        if (!$structure || !($structure instanceof self)) {
            return false;
        }

        $key = $for_request
            ? $structure->get_external_name()
            : $structure->get_name();

        if (!$key) {
            $this->set_error(self::ERR_MERGE, self::s2p_t('Couldn\'t extract structure name.'));

            return false;
        }

        if (empty($this->_merged_structures) || !is_array($this->_merged_structures)) {
            $this->_merged_structures = [];
        }

        $this->_merged_structures[$key] = $structure;

        return true;
    }

    /**
     * Parses a string buffer from server response
     *
     * @param string $scope_buf
     * @param mixed $parsing_params
     *
     * @return array|bool|mixed
     */
    public function extract_info_from_response_buffer(string $scope_buf, array $parsing_params = []) : ?array
    {
        if (!($scope_arr = @json_decode($scope_buf, true))
         || !is_array($scope_arr)) {
            return null;
        }

        return $this->extract_info_from_response_array($scope_arr, $parsing_params);
    }

    /**
     * Transform array keys from external_name to name and vice-versa.
     *
     * @param array $scope_arr Array to transform
     * @param mixed $parsing_params
     *
     * @return array|bool|mixed
     */
    public function transfrom_keys_to_external_names($scope_arr, $parsing_params = false)
    {
        if (!$this->_init_variable()) {
            return false;
        }

        if (empty($parsing_params) || !is_array($parsing_params)) {
            $parsing_params = [];
        }

        $parsing_params['check_external_names'] = false;

        if (!($return_arr = $this->_var->transform_keys($scope_arr, null, $parsing_params))
         || !is_array($return_arr)) {
            $return_arr = false;
        }

        if (!empty($this->_merged_structures) && is_array($this->_merged_structures)) {
            if (empty($return_arr)) {
                $return_arr = [];
            }

            /**
             * @var string $structure_name
             * @var S2P_SDK_Scope_Structure $structure_obj
             */
            foreach ($this->_merged_structures as $structure_name => $structure_obj) {
                if (!($structure_result_arr = $structure_obj->transfrom_keys_to_external_names($scope_arr, $parsing_params))
                 || !is_array($structure_result_arr)) {
                    continue;
                }

                $return_arr = array_merge($return_arr, $structure_result_arr);
            }
        }

        return $return_arr;
    }

    /**
     * Transform array keys from external_name to name and vice-versa.
     *
     * @param array $scope_arr Array to transform
     * @param mixed $parsing_params
     *
     * @return array|bool|mixed
     */
    public function transfrom_keys_to_internal_names($scope_arr, $parsing_params = false)
    {
        if (!$this->_init_variable()) {
            return false;
        }

        if (empty($parsing_params) || !is_array($parsing_params)) {
            $parsing_params = [];
        }

        $parsing_params['check_external_names'] = true;

        if (!($return_arr = $this->_var->transform_keys($scope_arr, null, $parsing_params))
         || !is_array($return_arr)) {
            $return_arr = false;
        }

        if (!empty($this->_merged_structures) && is_array($this->_merged_structures)) {
            if (empty($return_arr)) {
                $return_arr = [];
            }

            /**
             * @var string $structure_name
             * @var S2P_SDK_Scope_Structure $structure_obj
             */
            foreach ($this->_merged_structures as $structure_name => $structure_obj) {
                if (!($structure_result_arr = $structure_obj->transfrom_keys_to_internal_names($scope_arr, $parsing_params))
                 || !is_array($structure_result_arr)) {
                    continue;
                }

                $return_arr = array_merge($return_arr, $structure_result_arr);
            }
        }

        return $return_arr;
    }

    /**
     * Parses an array which was obtained from a json_decode from a server response body (or an emulated array ;) )
     * In case of parsing errors we try to extract as much information as possible from array.
     *
     * @param array $scope_arr
     * @param array|bool $parsing_params
     *
     * @return array|bool|mixed
     */
    public function extract_info_from_response_array($scope_arr, array $parsing_params = [])
    {
        if (!$this->_init_variable()) {
            return false;
        }

        $parsing_params['check_external_names'] = true;

        if (!($return_arr = $this->_var->extract_values($scope_arr, $parsing_params))
         || !is_array($return_arr)) {
            $return_arr = false;
        }

        if (!empty($this->_merged_structures) && is_array($this->_merged_structures)) {
            if (empty($return_arr)) {
                $return_arr = [];
            }

            /**
             * @var string $structure_name
             * @var S2P_SDK_Scope_Structure $structure_obj
             */
            foreach ($this->_merged_structures as $structure_name => $structure_obj) {
                if (!($structure_result_arr = $structure_obj->extract_info_from_response_array($scope_arr, $parsing_params))
                 || !is_array($structure_result_arr)) {
                    $this->copy_parsing_error($structure_obj);
                    continue;
                }

                if ($structure_obj->get_parsing_error()) {
                    $this->copy_parsing_error($structure_obj);
                }

                $return_arr = array_merge($return_arr, $structure_result_arr);
            }
        }

        return $return_arr;
    }

    /**
     * Parses an array of information and formats it according to structure definition using external_names as keys
     * If result has errors there is no use to send buggy request so we just return false.
     *
     * @param array $info_arr
     * @param array|bool $parsing_params
     *
     * @return array|bool
     */
    public function prepare_info_for_request_to_array($info_arr, $parsing_params = false)
    {
        if (!$this->_init_variable()) {
            return false;
        }

        if (empty($parsing_params) || !is_array($parsing_params)) {
            $parsing_params = [];
        }

        $parsing_params['check_external_names'] = false;

        if (!($return_arr = $this->_var->extract_values($info_arr, $parsing_params))
         || $this->_var->has_error()) {
            $return_arr = false;
        }

        if (!empty($this->_merged_structures) && is_array($this->_merged_structures)) {
            if (empty($return_arr)) {
                $return_arr = [];
            }

            /**
             * @var string $structure_name
             * @var S2P_SDK_Scope_Structure $structure_obj
             */
            foreach ($this->_merged_structures as $structure_name => $structure_obj) {
                if (!($structure_result_arr = $structure_obj->prepare_info_for_request_to_array($info_arr, $parsing_params))
                 || !is_array($structure_result_arr)) {
                    $this->copy_parsing_error($structure_obj);
                    continue;
                }

                if ($structure_obj->get_parsing_error()) {
                    $this->copy_parsing_error($structure_obj);
                }

                $return_arr = array_merge($return_arr, $structure_result_arr);
            }
        }

        return $return_arr;
    }

    public function prepare_info_for_request_to_buffer($info_arr, $parsing_params = false)
    {
        if (!($parsed_arr = $this->prepare_info_for_request_to_array($info_arr, $parsing_params))
         || !is_array($parsed_arr)) {
            return false;
        }

        return @json_encode($parsed_arr);
    }

    public function scope_to_path_objects($scope_arr = false, $params = false)
    {
        if (empty($params) || !is_array($params)) {
            $params = [];
        }

        if (empty($params['path'])) {
            $params['path'] = '';
        }
        if (empty($params['scope_external_names'])) {
            $params['scope_external_names'] = false;
        }
        if (empty($params['include_nodes_to_paths'])) {
            $params['include_nodes_to_paths'] = false;
        }

        if ($scope_arr === false) {
            if (empty($params['scope_external_names'])) {
                $extraction_arr = [];
                $extraction_arr['nullify_full_object'] = true;
                $extraction_arr['skip_regexps'] = true;

                $scope_arr = $this->extract_info_from_response_array(['foobar' => 1], $extraction_arr);
            } else {
                $extraction_arr = [];
                $extraction_arr['nullify_full_object'] = true;
                $extraction_arr['skip_regexps'] = true;

                $scope_arr = $this->prepare_info_for_request_to_array(['foobar' => 1], $extraction_arr);
            }
        }

        if (empty($scope_arr) || !is_array($scope_arr)) {
            return false;
        }

        $display_name_params = [];
        $display_name_params['check_external_names'] = $params['scope_external_names'];

        $return_arr = [];
        foreach ($scope_arr as $current_node => $node_arr) {
            $current_path = $params['path'].($params['path'] != '' ? '.' : '').$current_node;
            $current_path = preg_replace('@\.[0-9]+\.@', '.', $current_path);

            if (!is_array($node_arr)
             || (is_array($node_arr) && empty($node_arr))) {
                // For empty arrays or leafs
                $return_arr[$current_path] = $this->path_to_node_details($current_path, $display_name_params);
                continue;
            }

            if (empty($params['include_nodes_to_paths'])) {
                $return_arr[$current_path] = $node_arr;
            }

            // Complex structures...
            $new_params = $params;
            $new_params['path'] = $current_path;

            if (($node_paths = $this->scope_to_path_objects($node_arr, $new_params))
            && is_array($node_paths)) {
                $return_arr = array_merge($return_arr, $node_paths);
            }
        }

        return $return_arr;
    }

    public function path_to_display_name($path, array $params = [])
    {
        if (!($node_arr = $this->path_to_node_details($path, $params))) {
            return false;
        }

        if (!empty($node_arr['display_name'])) {
            return $node_arr['display_name'];
        }

        return '';
    }

    /**
     * Searches in current structure a node by it's path (eg. Method.ID). If path is found by walking structure definition, node details found at that position is returned
     *
     * @param string $path Path to search node (eg. Method.ID)
     * @param bool|false $params Method parameters
     *
     * @return bool|array
     */
    public function path_to_node_details($path, array $params = [])
    {
        if (empty($params['check_external_names'])) {
            $params['check_external_names'] = false;
        }
        if (empty($params['definition_arr'])) {
            $def_arr = $this->get_validated_definition();
            $params['definition_arr'] = $def_arr ? [] : [$def_arr];
        }
        if (empty($params['original_path'])) {
            $params['original_path'] = preg_replace('@\.[0-9]+\.@', '.', $path);
            $path = $params['original_path'];
        }
        if (empty($params['current_path'])) {
            $params['current_path'] = '';
        }

        if (empty($path)
         || empty($params['definition_arr']) || !is_array($params['definition_arr'])) {
            return false;
        }

        $definition_arr = $params['definition_arr'];

        if (empty($params['check_external_names'])) {
            $check_key = 'name';
        } else {
            $check_key = 'external_name';
        }

        if (!is_array($path)) {
            $path = explode('.', $path);
        }

        if (empty($path) || !isset($path[0])) {
            return false;
        }

        foreach ($definition_arr as $node_arr) {
            $current_path = $params['current_path'];
            if ($node_arr['type'] !== S2P_SDK_Scope_Variable::TYPE_BLOB_GROUP) {
                $current_path .= ($params['current_path'] !== '' ? '.' : '').$node_arr[$check_key];
            }

            if ($node_arr['type'] !== S2P_SDK_Scope_Variable::TYPE_BLOB_GROUP
            && !isset($path[1]) && !empty($node_arr[$check_key])
            // current node key is same as current path element
            && $node_arr[$check_key] == $path[0]
            // full paths match
            && $current_path === $params['original_path']) {
                return $node_arr;
            }

            if (!empty($node_arr['structure'])) {
                if ($node_arr['type'] === S2P_SDK_Scope_Variable::TYPE_BLOB_GROUP) {
                    $new_path = $path;
                } elseif (!isset($path[1])
                 || !($new_path = array_slice($path, 1))) {
                    continue;
                }

                $new_params = $params;
                $new_params['definition_arr'] = $node_arr['structure'];
                $new_params['current_path'] = $current_path;

                if (($recursive_result = $this->path_to_node_details($new_path, $new_params)) !== false) {
                    return $recursive_result;
                }
            }
        }

        return false;
    }

    /**
     * Returns false if there were no errors in variable or error array if any errors in $_var object
     *
     * @return null|array
     */
    public function get_parsing_error() : ?array
    {
        $this->_init_variable();

        if (!$this->has_error() && !$this->_var->has_error()) {
            return null;
        }

        return $this->has_error() ? $this->get_error() : $this->_var->get_error();
    }

    /**
     * Copies any parsing error from $obj to current object
     *
     * @param null|self $obj
     *
     * @return bool
     */
    public function copy_parsing_error(?self $obj) : bool
    {
        if (!$obj
            || !($error_arr = $obj->get_parsing_error())) {
            return false;
        }

        $this->copy_error_from_array($error_arr);

        return true;
    }

    private function _init_variable() : ?S2P_SDK_Scope_Variable
    {
        if ($this->_var === null) {
            $this->_var = new S2P_SDK_Scope_Variable($this->get_validated_definition());
        }

        if (empty($this->_var)) {
            $this->_var = null;
            $this->set_error(self::ERR_VARIABLE, self::s2p_t('Couldn\'t initialize parsing variable.'));

            return null;
        }

        return $this->_var;
    }
}
