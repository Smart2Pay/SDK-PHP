<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_CLASSES' ) or !defined( 'S2P_SDK_DIR_STRUCTURES' ) or !defined( 'S2P_SDK_DIR_PATH' ) )
    die( 'Something went bad' );

include_once( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' );
include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_values_source.inc.php' );
include_once( S2P_SDK_DIR_STRUCTURES.'s2p_sdk_scope_variable.inc.php' );

class S2P_SDK_Demo extends S2P_SDK_Module
{
    const ALLOW_REMOTE_CALLS = true;

    const ERR_BASE_URL = 1, ERR_INSTANTIATE_METHOD = 2, ERR_FUNCTIONALITY = 3,
          ERR_APIKEY = 4, ERR_ENVIRONMENT = 5;

    /** @var string $_base_url */
    private $_base_url = '';

    /** @var S2P_SDK_Method $_method */
    private $_method = null;

    /** @var string $_method_func */
    private $_method_func = '';

    /** @var array $_method_func_details */
    private $_method_func_details = null;

    /** @var array $_post_data */
    private $_post_data = null;

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed If this function returns false it will consider module is not initialized correctly and will not return class instance
     */
    public function init( $module_params = false )
    {
        $this->reset_demo();

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
    }

    private function reset_demo()
    {
        $this->_base_url = '';
        $this->_method = null;
        $this->_method_func = '';
        $this->_method_func_details = null;
        $this->_post_data = null;
    }

    // We assume all calls to Demo class are made using full URL to demo.php file in root directory of SDK...
    public static function guess_base_url()
    {
        if( empty( $_SERVER['HTTP_HOST'] ) )
            return '';

        $url = 'http://'.$_SERVER['HTTP_HOST'];

        $path = '';
        if( !empty( $_SERVER['SCRIPT_NAME'] ) )
        {
            $path = dirname( $_SERVER['SCRIPT_NAME'] );

            if( substr( $path, -1 ) == '/' )
                $path = substr( $path, 0, -1 );

            if( substr( $path, -7 ) == 'samples' )
                $path = substr( $path, 0, -7 );
        }

        $url .= $path;

        if( substr( $url, -1 ) != '/' )
            $url .= '/';

        return $url;
    }

    public function base_url( $url = null )
    {
        if( $url === null )
        {
            if( empty( $this->_base_url ) )
                $this->_base_url = self::guess_base_url();

            return $this->_base_url;
        }

        $this->_base_url = $url;
        return $this->_base_url;
    }

    public static function default_post_data()
    {
        return array(
            'foobar' => 0,
            'api_key' => '',
            'environment' => 'test',
            'method' => '',
            'func' => '',
            'gvars' => array(),
            'gvars_arrays' => array(),
            'mparams' => array(),
            'mparams_arrays' => array(),
            'do_submit' => 0,
        );
    }

    public static function validate_post_data( $post_arr )
    {
        $default_var = self::default_post_data();

        if( empty( $post_arr ) or !is_array( $post_arr ) )
            return $default_var;

        foreach( $default_var as $key => $val )
        {
            if( !array_key_exists( $key, $post_arr ) )
                $post_arr[$key] = $val;
        }

        return $post_arr;
    }

    public static function extract_post_data()
    {
        $default_var = self::default_post_data();

        $post_vars = array();
        foreach( $default_var as $key => $val )
        {
            if( array_key_exists( $key, $_POST ) )
                $post_vars[$key] = $_POST[$key];
            else
                $post_vars[$key] = $val;
        }

        return $post_vars;
    }

    public function init_method( $method )
    {
        $this->_post_data = array();

        $this->reset_error();

        $method = trim( $method );
        /** @var S2P_SDK_Method $instance */
        if( empty( $method )
         or !($instance = self::get_instance( 'S2P_SDK_Meth_'.ucfirst( $method ), null, false )) )
        {
            if( self::st_has_error() )
                $this->copy_static_error();

            else
                $this->set_error( self::ERR_INSTANTIATE_METHOD, self::s2p_t( 'Error instantiating method %s.', ucfirst( $method ) ) );

            return false;
        }

        $this->_method = $instance;

        return true;
    }

    public function init_functionality( $func )
    {
        $this->_post_data = array();

        $this->reset_error();

        $func = trim( $func );
        if( empty( $this->_method ) )
        {
            $this->set_error( self::ERR_INSTANTIATE_METHOD, self::s2p_t( 'Initialize method first.' ) );
            return false;
        }

        if( !($func_details = $this->_method->valid_functionality( $func )) )
        {
            $this->set_error( self::ERR_INSTANTIATE_METHOD, self::s2p_t( 'Invalid functionality %s.', $func ) );
            return false;
        }

        $this->_method_func = $func;
        $this->_method_func_details = $func_details;

        return true;
    }

    public static function form_str( $str )
    {
        return str_replace( '"', '&quot;', $str );
    }

    private function get_form_common_fields( $post_arr, $form_arr )
    {
        $post_arr = self::validate_post_data( $post_arr );

        if( empty( $post_arr['foobar'] ) )
        {
            $api_config_arr = self::get_api_configuration();

            // form defaults
            if( empty( $post_arr['api_key'] ) and !empty( $api_config_arr['api_key'] ) )
                $post_arr['api_key'] = $api_config_arr['api_key'];
            if( empty( $post_arr['environment'] ) and !empty( $api_config_arr['environment'] ) )
                $post_arr['environment'] = $api_config_arr['environment'];
        }

        ob_start();
        ?>
        <input type="hidden" name="foobar" value="1" />
        <div class="form_field">
            <label for="api_key"><?php echo self::s2p_t( 'API Key' )?></label>
            <div class="form_input"><input type="text" id="api_key" name="api_key" value="<?php echo self::form_str( $post_arr['api_key'] )?>" style="width: 350px;" /></div>
        </div>

        <div class="form_field">
            <label for="environment"><?php echo self::s2p_t( 'Environment' )?></label>
            <div class="form_input"><select id="environment" name="environment">
                <option value="test" <?php echo ($post_arr['environment']=='test'?'selected="selected"':'')?>>Test</option>
                <option value="live" <?php echo ($post_arr['environment']=='live'?'selected="selected"':'')?>>Live</option>
            </select></div>
        </div>

        <div class="form_field">
            <label for="method"><?php echo self::s2p_t( 'Method' )?></label>
            <div class="form_input"><select id="method" name="method" onchange="document.<?php echo $form_arr['form_name']?>.submit()">
                <option value=""> - <?php echo self::s2p_t( 'Choose an option' );?> - </option><?php
                if( ($all_methods = S2P_SDK_Method::get_all_methods())
                and is_array( $all_methods ) )
                {
                    foreach( $all_methods as $method_id => $method_details )
                    {
                        if( empty( $method_details['instance'] ) )
                            continue;

                        /** @var S2P_SDK_Method $instance */
                        $instance = $method_details['instance'];

                        ?><option value="<?php echo $method_id?>" <?php echo ($post_arr['method'] == $method_id?'selected="selected"':'')?>><?php echo $method_id.' - '.$instance->get_name()?></option><?php
                    }
                }
            ?></select></div>
        </div>

        <?php
        if( !empty( $this->_method )
        and ($method_functionalities = $this->_method->get_functionalities())
        and is_array( $method_functionalities ) )
        {
            ?>
            <div class="form_field">
            <label for="func"><?php echo self::s2p_t( 'Functionality' )?></label>

            <div class="form_input">
            <select id="func" name="func" onchange="document.<?php echo $form_arr['form_name']?>.submit()">
            <option value=""> - <?php echo self::s2p_t( 'Choose an option' );?> -</option><?php
                foreach( $method_functionalities as $functionality_name => $functionality_arr )
                {
                    if( empty( $method_details['instance'] ) )
                        continue;

                    /** @var S2P_SDK_Method $instance */
                    $instance = $method_details['instance'];

                    ?>
                    <option value="<?php echo $functionality_name?>" <?php echo( $post_arr['func'] == $functionality_name ? 'selected="selected"' : '' )?>><?php echo $functionality_name . ' - ' . $functionality_arr['name']?></option><?php
                }
            ?></select></div>
            </div>
        <?php
        }
        $buf = ob_get_clean();

        return $buf;
    }

    private function validate_form_method_get_params_fields( $post_arr, $submit_result_arr )
    {
        if( empty( $this->_method )
         or empty( $this->_method_func )
         or !($func_details = $this->_method_func_details)
         or empty( $func_details['get_variables'] ) or !is_array( $func_details['get_variables'] ) )
            return $submit_result_arr;

        $post_arr = self::validate_post_data( $post_arr );
        $submit_result_arr = self::validate_submit_result( $submit_result_arr );

        $value_source_obj = new S2P_SDK_Values_Source();

        foreach( $func_details['get_variables'] as $get_var )
        {
            if( !array_key_exists( $get_var['name'], $post_arr['gvars'] ) )
            {
                if( !empty( $get_var['mandatory'] ) )
                    $submit_result_arr['errors_arr']['gvars'][ $get_var['name'] ] = self::s2p_t( 'Mandatory field %s not provided.', $get_var['name'] );

                continue;
            }

            if( !empty( $get_var['regexp'] )
            and !preg_match( '/'.$get_var['regexp'].'/', $post_arr['gvars'][$get_var['name']] ) )
            {
                $submit_result_arr['errors_arr']['gvars'][ $get_var['name'] ] = self::s2p_t( 'Field %s failed regular expression %s.', $get_var['name'], $get_var['regexp'] );
                continue;
            }

            $var_value = S2P_SDK_Scope_Variable::scalar_value( $get_var['type'], $post_arr['gvars'][$get_var['name']], $get_var['array_type'], $get_var['array_numeric_keys'] );
            $default_var_value = null;
            if( array_key_exists( 'default', $get_var ) )
                $default_var_value = S2P_SDK_Scope_Variable::scalar_value( $get_var['type'], $get_var['default'], $get_var['array_type'], $get_var['array_numeric_keys'] );

            if( !empty( $get_var['skip_if_default'] )
            and $var_value === $default_var_value )
                continue;

            if( !empty( $get_var['value_source'] ) and $value_source_obj::valid_type( $get_var['value_source'] ) )
            {
                $value_source_obj->source_type( $get_var['value_source'] );
                if( !$value_source_obj->valid_value( $var_value ) )
                {
                    $submit_result_arr['errors_arr']['gvars'][ $get_var['name'] ] = self::s2p_t( 'Variable %s contains invalid value [%s].', $get_var['name'], $var_value );
                    continue;
                }
            }
        }

        return $submit_result_arr;
    }

    private function get_form_method_get_params_fields( $post_arr, $form_arr )
    {
        if( empty( $this->_method )
         or empty( $form_arr ) or !is_array( $form_arr ) or empty( $form_arr['form_name'] )
         or empty( $this->_method_func )
         or !($func_details = $this->_method_func_details)
         or empty( $func_details['get_variables'] ) or !is_array( $func_details['get_variables'] ) )
            return '';

        $post_arr = self::validate_post_data( $post_arr );

        ob_start();
        ?>
        <fieldset id="method_get_parameters">
        <label for="method_get_parameters"><a href="javascript:void(0);" onclick="toggle_container( 'method_get_parameters_container' )"><strong><?php echo self::s2p_t( 'Get parameters' )?></strong></a></label>
        <div id="method_get_parameters_container" style="display: block;">

        <?php
        foreach( $func_details['get_variables'] as $get_var )
        {
            $field_name = 'gvars['.$get_var['name'].']';
            $field_id = 'gvar_'.$get_var['name'];

            if( isset( $post_arr['gvars'][$get_var['name']] ) )
                $field_value = $post_arr['gvars'][$get_var['name']];
            elseif( !empty( $get_var['check_constant'] ) and defined( $get_var['check_constant'] ) )
                $field_value = constant( $get_var['check_constant'] );
            elseif( isset( $get_var['default'] ) )
                $field_value = $get_var['default'];
            else
                $field_value = '';

            if( !($field_type_arr = S2P_SDK_Scope_Variable::valid_type( $get_var['type'] )) )
                $field_type_arr = array( 'title' => '[undefined]' );

            ?>
            <div class="form_field">
                <label for="<?php echo $field_id?>" class="<?php echo (!empty( $get_var['mandatory'] )?'mandatory':'')?>"><?php echo $get_var['name']?></label>
                <div class="form_input"><?php
                if( !empty( $get_var['value_source'] )
                and S2P_SDK_Values_Source::valid_type( $get_var['value_source'] )
                and ($value_source_obj = new S2P_SDK_Values_Source( $get_var['value_source'] ))
                // make sure we don't stop here...
                and ($value_source_obj->remote_calls( self::ALLOW_REMOTE_CALLS ) or true)
                and ($options_value = $value_source_obj->get_option_values())
                and is_array( $options_value ) )
                {
                    ?><select id="<?php echo $field_id?>" name="<?php echo $field_name?>">
                    <option value=""> - <?php echo self::s2p_t( 'Choose an option' );?> [<?php echo count( $options_value)?>] - </option><?php
                    foreach( $options_value as $key => $val )
                    {
                        ?><option value="<?php echo self::form_str( $key );?>" <?php echo ($field_value == $key?'selected="selected"':'')?>><?php echo $val;?></option><?php
                    }
                    ?></select><?php
                } else
                {
                    if( $get_var['type'] == S2P_SDK_Scope_Variable::TYPE_BOOL )
                    {
                        ?><input type="checkbox" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="1" <?php echo (!empty( $field_value )?'checked="checked"':'')?> /><?php
                    } elseif( $get_var['type'] == S2P_SDK_Scope_Variable::TYPE_DATETIME )
                    {
                        ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" class="datepicker" /><?php
                    } else
                    {
                        ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" /><?php
                    }

                    echo ' ('.$field_type_arr['title'].')';
                }

                if( $get_var['type'] == S2P_SDK_Scope_Variable::TYPE_DATETIME )
                {
                    echo ' - yyyymmddhhmmss';
                }
                ?></div>
            </div>
            <?php
        }
        ?>
        </div>
        </fieldset>
        <?php
        $buf = ob_get_clean();

        return $buf;
    }

    private function get_form_method_parameters_fields( $post_arr, $form_arr )
    {
        if( empty( $this->_method )
         or empty( $form_arr ) or ! is_array( $form_arr ) or empty( $form_arr['form_name'] )
         or empty( $post_arr['func'] )
         or ! ( $func_details = $this->_method->valid_functionality( $post_arr['func'] ) )
         or empty( $func_details['request_structure'] ) )
            return '';

        $post_arr = self::validate_post_data( $post_arr );

        /** @var S2P_SDK_Scope_Structure $structure_obj */
        $structure_obj = $func_details['request_structure'];

        if( ($method_definition = $structure_obj->get_validated_definition()) )
        {
            if( empty( $func_details['mandatory_in_request'] )
             or !($mandatory_arr = $structure_obj->transfrom_keys_to_internal_names( $func_details['mandatory_in_request'] )) )
                $mandatory_arr = array();

            if( empty( $func_details['hide_in_request'] )
             or !($hide_keys_arr = $structure_obj->transfrom_keys_to_internal_names( $func_details['hide_in_request'] )) )
                $hide_keys_arr = array();

            ob_start();
            ?>
            <fieldset id="method_parameters">
            <label for="method_parameters"><a href="javascript:void(0);" onclick="toggle_container( 'method_parameters_container' )"><strong><?php echo self::s2p_t( 'Method parameters' )?></strong></a></label>
            <div id="method_parameters_container" style="display: block;">

            <?php echo $this->get_form_method_parameters_fields_detailed( $method_definition, $mandatory_arr, $hide_keys_arr, $post_arr, $form_arr );?>

            </div>
            </fieldset>
            <?php
            $buf = ob_get_clean();

            return $buf;
        }

        return '';

    }

    public static function transform_post_arrays( $post_arr, $dotted_paths_arr )
    {
        if( empty( $post_arr ) or !is_array( $post_arr )
         or empty( $dotted_paths_arr ) or !is_array( $dotted_paths_arr ) )
            return $post_arr;

        foreach( $dotted_paths_arr as $dotted_path )
        {
            if( empty( $dotted_path )
             or !($dotted_path_arr = explode( '.', str_replace( '..', '.', $dotted_path ) )) )
                continue;

            if( !($post_arr = self::transform_post_array( $post_arr, $dotted_path_arr )) )
                return false;
        }

        return $post_arr;
    }

    private static function transform_post_array( $post_arr, $dotted_path_arr )
    {
        if( empty( $post_arr ) or !is_array( $dotted_path_arr ) )
            return null;

        if( !isset( $dotted_path_arr[0] ) )
            $current_index = null;

        else
        {
            $current_index = $dotted_path_arr[0];

            if( !array_key_exists( $current_index, $post_arr ) )
                return $post_arr;
        }

        if( $current_index === null )
        {
            // we reached a leaf... try converting values...
            if( !is_array( $post_arr )
             or empty( $post_arr['vals'] ) or !is_array( $post_arr['vals'] ) )
                return null;

            // for numeric array keys
            if( empty( $post_arr['keys'] ) or !is_array( $post_arr['keys'] ) )
                $post_arr['keys'] = array();

            $transformed_arr = array();
            foreach( $post_arr['vals'] as $key => $val )
            {
                if( !isset( $post_arr['keys'][$key] ) )
                    $transformed_arr[] = $val;
                else
                {
                    if( $post_arr['keys'][$key] == '' )
                        continue;

                    $transformed_arr[(string)$post_arr['keys'][$key]] = $val;
                }
            }

            return $transformed_arr;
        }

        if( is_array( $post_arr[$current_index] ) and !empty( $dotted_path_arr ) )
        {
            if( array_shift( $dotted_path_arr ) === null )
                return null;

            $post_arr[$current_index] = self::transform_post_array( $post_arr[$current_index], $dotted_path_arr );

            return $post_arr;
        }

        return null;
    }

    public static function extract_field_value( $post_arr, $dotted_path )
    {
        if( empty( $post_arr ) )
            return null;

        if( !is_array( $dotted_path ) and !is_string( $dotted_path ) )
            return null;

        if( is_string( $dotted_path ) )
            $dotted_path = explode( '.', str_replace( '..', '.', $dotted_path ) );

        if( empty( $dotted_path ) or !is_array( $dotted_path ) )
            return null;

        $current_index = $dotted_path[0];

        if( !array_key_exists( $current_index, $post_arr ) )
            return null;

        if( is_scalar( $post_arr[$current_index] ) or !isset( $dotted_path[1] ) )
            return $post_arr[$current_index];

        if( is_array( $post_arr[$current_index] ) and isset( $dotted_path[1] ) )
        {
            if( array_shift( $dotted_path ) === null )
                return null;

            return self::extract_field_value( $post_arr[$current_index], $dotted_path );
        }

        return null;
    }

    private function get_form_method_parameters_fields_detailed( $structure_definition, $mandatory_arr, $hide_keys_arr, $post_arr, $form_arr, $params = false )
    {
        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['path'] ) )
            $params['path'] = '';
        if( empty( $params['read_path'] ) )
            $params['read_path'] = '';
        if( empty( $params['name'] ) )
            $params['name'] = '';
        if( !isset( $params['blob_array_index'] ) )
            $params['blob_array_index'] = false;
        if( !isset( $params['blob_array_index_read'] ) )
            $params['blob_array_index_read'] = false;
        if( empty( $params['level'] ) )
            $params['level'] = -1;

        if( empty( $params['blob_array_index_read'] ) and !empty( $params['blob_array_index'] ) )
            $params['blob_array_index_read'] = $params['blob_array_index'];

        if( empty( $mandatory_arr ) or !is_array( $mandatory_arr ) )
            $mandatory_arr = array();
        if( empty( $hide_keys_arr ) or !is_array( $hide_keys_arr ) )
            $hide_keys_arr = array();

        if( empty( $structure_definition ) or !is_array( $structure_definition )
         or (array_key_exists( $structure_definition['name'], $hide_keys_arr )
                        and !is_array( $hide_keys_arr[$structure_definition['name']] )) )
            return '';

        $params['path'] .= (!empty( $params['path'] )?'.':'').($params['blob_array_index']!==false?$params['blob_array_index'].'.':'').$structure_definition['name'];
        $params['read_path'] .= (!empty( $params['read_path'] )?'.':'').($params['blob_array_index_read']!==false?$params['blob_array_index_read'].'.':'').$structure_definition['name'];
        $params['name'] .= ($params['blob_array_index']!==false?'['.$params['blob_array_index'].']':'').'['.$structure_definition['name'].']';
        $params['level']++;

        if( empty( $structure_definition['structure'] ) or !is_array( $structure_definition['structure'] ) )
        {
            // display single element...
            $field_id = str_replace( array( '.', '[', ']' ), '_', $params['path'] );
            $field_name = 'mparams'.$params['name'];
            $field_value = self::extract_field_value( $post_arr['mparams'], $params['read_path'] );

            if( $field_value === null )
            {
                if( !empty( $structure_definition['check_constant'] ) and defined( $structure_definition['check_constant'] ) )
                    $field_value = constant( $structure_definition['check_constant'] );
                else
                    $field_value = '';
            }

            $field_mandatory = false;
            if( array_key_exists( $structure_definition['name'], $mandatory_arr ) )
                $field_mandatory = true;

            ob_start();
            ?>
            <div class="form_field">
                <label for="<?php echo $field_id?>" title="<?php echo self::form_str( $params['path'] )?>" class="<?php echo (!empty( $field_mandatory )?'mandatory':'')?>"><?php echo $structure_definition['name']?></label>
                <div class="form_input"><?php

                    if( empty( $structure_definition['value_source'] )
                     or !S2P_SDK_Values_Source::valid_type( $structure_definition['value_source'] )
                     or !($value_source_obj = new S2P_SDK_Values_Source( $structure_definition['value_source'] ))
                     // make sure we don't stop here...
                     or ($value_source_obj->remote_calls( self::ALLOW_REMOTE_CALLS ) and false)
                     or !($options_value = $value_source_obj->get_option_values())
                     or !is_array( $options_value ) )
                        $options_value = array();

                    if( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_BOOL )
                    {
                        ?><input type="checkbox" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="1" <?php echo (!empty( $field_value )?'checked="checked"':'')?> /><?php
                    } elseif( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_DATETIME )
                    {
                        ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" class="datepicker" /><?php
                    } elseif( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_ARRAY )
                    {
                        if( empty( $field_value ) or !is_array( $field_value ) )
                            $field_value = array();

                        if( empty( $field_value['keys'] ) or !is_array( $field_value['keys'] ) )
                            $field_value['keys'] = array();
                        if( empty( $field_value['vals'] ) or !is_array( $field_value['vals'] ) )
                            $field_value['vals'] = array();

                        ?>
                        <input type="hidden" name="mparams_arrays[]" value="<?php echo self::form_str( $params['path'] )?>" />
                        <div id="<?php echo $field_id?>___container">
                        <?php
                        foreach( $field_value['vals'] as $key => $val )
                        {
                            $field_key = '';
                            if( !empty( $field_value['keys'][$key] ) )
                                $field_key = $field_value['keys'][$key];

                            ?>
                            <div class="form_input_array">
                            <?php
                            if( empty( $structure_definition['array_numeric_keys'] ) )
                            {
                                ?><input type="text" name="<?php echo $field_name?>[keys][]" value="<?php echo self::form_str( $field_key )?>" placeholder="<?php echo self::form_str( self::s2p_t( 'Key' ) )?>" /><?php
                            }

                            $options_params = array();
                            $options_params['field_id'] = false;
                            $options_params['field_name'] = $field_name.'[vals][]';
                            $options_params['field_value'] = $val;

                            if( !empty( $options_value )
                                and ($select_buf = self::display_select_options( $options_value, $options_params )) !== false )
                                echo $select_buf;

                            else
                            {
                                if( empty( $structure_definition['array_type'] )
                                    or !($field_type_arr = S2P_SDK_Scope_Variable::valid_type( $structure_definition['array_type'] )) )
                                    $field_type_arr = array( 'title' => 'string' );

                                ?>
                                <input type="text" name="<?php echo $field_name?>[vals][]" value="<?php echo self::form_str( $val )?>" placeholder="<?php echo self::form_str( self::s2p_t( 'Value' ) );?>" />
                                (<?php echo $field_type_arr['title']?>)
                                <?php
                            }
                            ?>
                            <a href="javascript:void(0);" onclick="remove_methods_array_element( $(this), '<?php echo $field_id?>' )"><?php echo self::s2p_t( 'Remove' )?></a>
                            </div>
                            <?php
                        }
                        ?>
                        </div>
                        <div class="form_input_array input_disabler_container" id="<?php echo $field_id?>___template" style="display: none;">
                            <?php
                            if( empty( $structure_definition['array_numeric_keys'] ) )
                            {
                                ?><input type="text" name="<?php echo $field_name?>[keys][]" value="" placeholder="<?php echo self::form_str( self::s2p_t( 'Key' ) )?>" /><?php
                            }

                            $options_params = array();
                            $options_params['field_id'] = false;
                            $options_params['field_name'] = $field_name.'[vals][]';
                            $options_params['field_value'] = '';

                            if( !empty( $options_value )
                            and ($select_buf = self::display_select_options( $options_value, $options_params )) !== false )
                                echo $select_buf;

                            else
                            {
                                if( empty( $structure_definition['array_type'] )
                                 or !($field_type_arr = S2P_SDK_Scope_Variable::valid_type( $structure_definition['array_type'] )) )
                                    $field_type_arr = array( 'title' => 'string' );

                                ?>
                                <input type="text" name="<?php echo $field_name?>[vals][]" value="" placeholder="<?php echo self::form_str( self::s2p_t( 'Value' ) );?>" />
                                (<?php echo $field_type_arr['title']?>)
                                <?php
                            }
                            ?>
                            <a href="javascript:void(0);" onclick="remove_methods_array_element( $(this), '<?php echo $field_id?>' )"><?php echo self::s2p_t( 'Remove' )?></a>
                        </div>
                        <div id="<?php echo $field_id?>" class="field_adder_container"><a href="javascript:void(0);" onclick="add_methods_array_element( '<?php echo $field_id?>' )"><?php echo self::s2p_t( 'Add value' )?></a></div>
                        <?php
                    } else
                    {
                        $options_params = array();
                        $options_params['field_id'] = $field_id;
                        $options_params['field_name'] = $field_name;
                        $options_params['field_value'] = $field_value;

                        if( !empty( $options_value )
                        and ($select_buf = self::display_select_options( $options_value, $options_params )) !== false )
                            echo $select_buf;

                        else
                        {
                            if( !($field_type_arr = S2P_SDK_Scope_Variable::valid_type( $structure_definition['type'] )) )
                                $field_type_arr = array( 'title' => '[undefined]' );

                            ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" /><?php

                            echo ' ('.$field_type_arr['title'].')';
                        }
                    }

                    if( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_DATETIME )
                    {
                        echo ' - yyyymmddhhmmss';
                    }

                    if( !empty( $structure_definition['regexp'] ) )
                    {
                        echo ' <span class="form_input_regexp"><a href="javascript:void(0);" onclick="toggle_regexp( $(this) )" tabindex="10000">RExp</a><span class="form_input_regexp_exp">'.$structure_definition['regexp'].'</span></span>';
                    }
                ?></div>
            </div>
            <?php

            $buf = ob_get_clean();

            return $buf;
        }

        $field_id = str_replace( '.', '_', $params['path'] );

        ?>
        <fieldset id="mparam_<?php echo $field_id?>">
        <label for="mparam_<?php echo $field_id?>"><a href="javascript:void(0);" onclick="toggle_container( 'mparam_container_<?php echo $field_id?>' )"><strong><?php echo $params['path']?></strong></a></label>
        <div id="mparam_container_<?php echo $field_id?>" style="display: block;">
        <?php

        $new_mandatory_arr = array();
        if( array_key_exists( $structure_definition['name'], $mandatory_arr ) )
            $new_mandatory_arr = $mandatory_arr[ $structure_definition['name'] ];
        $new_hide_keys_arr = array();
        if( array_key_exists( $structure_definition['name'], $hide_keys_arr ) )
            $new_hide_keys_arr = $hide_keys_arr[ $structure_definition['name'] ];

        if( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_BLOB_ARRAY )
        {
            $blob_array_count = 0;
            if( ($blob_array_value = self::extract_field_value( $post_arr['mparams'], $params['path'] ))
            and is_array( $blob_array_value ) )
            {
                $elements_params = $params;
                $elements_params['blob_array_index'] = 0;
                $elements_params['blob_array_index_read'] = 0;

                foreach( $blob_array_value as $element_key => $element_arr )
                {
                    $elements_params['blob_array_index_read'] = $element_key;

                    ?><div class="form_input_blob_array"><?php
                    foreach( $structure_definition['structure'] as $element_definition )
                    {
                        if( ($element_buffer = $this->get_form_method_parameters_fields_detailed( $element_definition,
                            $new_mandatory_arr,
                            $new_hide_keys_arr,
                            $post_arr,
                            $form_arr,
                            $elements_params ) ) )
                            echo $element_buffer;
                    }
                    ?>
                    <a href="javascript:void(0);" onclick="remove_methods_blob_array_element( $(this), '<?php echo $field_id?>' )"><?php echo self::s2p_t( 'Remove' )?></a>
                    </div>
                    <?php

                    $elements_params['blob_array_index']++;
                    $blob_array_count++;
                }
            }
            ?>
            </div>
            <div id="mparam_container_<?php echo $field_id?>___template" class="form_input_blob_array input_disabler_container" style="display: none;">
            <?php
        }

        if( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_BLOB_ARRAY )
            $params['name'] .= '[{*BLOB_ARRAY_INDEX*}]';

        foreach( $structure_definition['structure'] as $element_definition )
        {
            if( ($element_buffer = $this->get_form_method_parameters_fields_detailed( $element_definition,
                $new_mandatory_arr,
                $new_hide_keys_arr,
                $post_arr,
                $form_arr,
                $params ) ) )
                echo $element_buffer;
        }

        if( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_BLOB_ARRAY )
        {
            ?>
            <a href="javascript:void(0);" onclick="remove_methods_blob_array_element( $(this), '<?php echo $field_id?>' )"><?php echo self::s2p_t( 'Remove' )?></a>
            <?php
        }

        ?></div><?php

        if( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_BLOB_ARRAY )
        {
            ?>
            <div class="field_adder_container"><a href="javascript:void(0);" onclick="add_methods_blob_array_element( '<?php echo $field_id?>' )"><?php echo self::s2p_t( 'Add value' )?></a></div>
            <?php
        }

        ?></fieldset><?php
        $structure_buffer = ob_get_clean();

        return $structure_buffer;
    }

    public static function display_select_options( $options_arr, $params )
    {
        if( empty( $options_arr ) or !is_array( $options_arr )
         or empty( $params ) or !is_array( $params )
         or !isset( $params['field_id'] ) or empty( $params['field_name'] ) )
            return false;

        if( empty( $params['style'] ) )
            $params['style'] = '';
        if( empty( $params['field_id'] ) )
            $params['field_id'] = false;
        if( !isset( $params['field_value'] ) )
            $params['field_value'] = '';
        if( !isset( $params['field_disabled'] ) )
            $params['field_disabled'] = false;

        ob_start();
        ?><select <?php echo (!empty( $params['field_id'] )?' id="'.$params['field_id'].'"':'')?> name="<?php echo $params['field_name']?>" <?php echo (!empty( $params['field_disabled'] )?'disabled="disabled"':'')?>>
        <option value=""> - <?php echo self::s2p_t( 'Choose an option' );?> [<?php echo count( $options_arr )?>] - </option><?php
        foreach( $options_arr as $key => $val )
        {
            ?><option value="<?php echo self::form_str( $key )?>" <?php echo ($params['field_value']==$key?'selected="selected"':'');?> style="<?php echo self::form_str( $params['style'] )?>"><?php echo $val;?></option><?php
        }
        ?></select><?php
        $buf = ob_get_clean();

        return $buf;
    }

    public static function default_submit_result()
    {
        return array(
            'form_submitted' => false,
            'errors_arr' => array(),
            'warnings_arr' => array(),
            'success_arr' => array(),
            'post_arr' => array(),
            'finalize_result' => array(),
            'api_obj' => null,
        );
    }

    public static function validate_submit_result( $submit_arr )
    {
        $default_var = self::default_submit_result();

        if( empty( $submit_arr ) or !is_array( $submit_arr ) )
            return $default_var;

        foreach( $default_var as $key => $val )
        {
            if( !array_key_exists( $key, $submit_arr ) )
                $submit_arr[$key] = $val;
        }

        return $submit_arr;
    }

    public function handle_submit( $params = false )
    {
        $this->reset_error();

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['post_arr'] ) )
            $params['post_arr'] = self::extract_post_data();

        if( !is_array( $params['post_arr'] ) )
            $params['post_arr'] = array();

        $post_arr = $params['post_arr'];

        $return_arr = self::default_submit_result();
        $return_arr['post_arr'] = $post_arr;

        if( empty( $post_arr['do_submit'] ) )
            return $return_arr;

        $return_arr['form_submitted'] = true;

        if( empty( $post_arr['method'] )
         or !$this->init_method( $post_arr['method'] ) )
        {
            // Reset error generated by method initialization
            $this->reset_error();

            $post_arr['method'] = '';

            $return_arr['errors_arr'][] = self::s2p_t( 'Invalid API method.' );
        }

        if( !empty( $post_arr['func'] )
        and !$this->init_functionality( $post_arr['func'] ) )
            $post_arr['func'] = '';

        // check mandatory fields...
        if( empty( $post_arr['api_key'] ) )
            $return_arr['errors_arr'][] = self::s2p_t( 'Invalid API key.' );

        if( empty( $post_arr['environment'] ) or !in_array( $post_arr['environment'], array( 'live', 'test' ) ) )
            $return_arr['errors_arr'][] = self::s2p_t( 'Invalid API environment.' );

        //
        //  Transform form variables in object variables
        //
        if( !empty( $post_arr['gvars_arrays'] ) and is_array( $post_arr['gvars_arrays'] ) )
        {
            if( ($gvars_post_arr = self::transform_post_arrays( $post_arr['gvars'], $post_arr['gvars_arrays'] )) )
                $post_arr['gvars'] = $gvars_post_arr;
            else
                $return_arr['errors_arr'][] = self::s2p_t( 'Couldn\'t extract get parameters.' );
        }

        if( !empty( $post_arr['mparams_arrays'] ) and is_array( $post_arr['mparams_arrays'] ) )
        {
            if( ($mparams_post_arr = self::transform_post_arrays( $post_arr['mparams'], $post_arr['mparams_arrays'] )) )
                $post_arr['mparams'] = $mparams_post_arr;
            else
                $return_arr['errors_arr'][] = self::s2p_t( 'Couldn\'t extract method parameters.' );
        }
        //
        //  END Transform form variables in object variables
        //

        $return_arr['post_arr'] = $post_arr;

        // If we have basic errors to display don't let script continue;
        if( !empty( $return_arr['errors_arr'] ) )
            return $return_arr;

        if( ($new_return_arr = $this->validate_form_method_get_params_fields( $post_arr, $return_arr ))
        and is_array( $new_return_arr ) )
        {
            if( !empty( $new_return_arr['errors_arr'] ) )
                $return_arr['errors_arr'] = array_merge( $return_arr['errors_arr'], $new_return_arr['errors_arr'] );
            if( !empty( $new_return_arr['warnings_arr'] ) )
                $return_arr['warnings_arr'] = array_merge( $return_arr['warnings_arr'], $new_return_arr['warnings_arr'] );
            if( !empty( $new_return_arr['success_arr'] ) )
                $return_arr['success_arr'] = array_merge( $return_arr['success_arr'], $new_return_arr['success_arr'] );
        }

        if( empty( $return_arr['errors_arr'] ) )
        {
            $api_params = array();
            $api_params['api_key'] = $post_arr['api_key'];
            $api_params['environment'] = $post_arr['environment'];

            $api_params['method'] = $post_arr['method'];
            $api_params['func'] = $post_arr['func'];

            $api_params['get_variables'] = $post_arr['gvars'];
            $api_params['method_params'] = $post_arr['mparams'];

            /** @var S2P_SDK_API $api */
            if( !($api = self::get_instance( 'S2P_SDK_API', $api_params, false )) )
            {
                if( ($error_arr = self::st_get_error()) and is_array( $error_arr ) )
                    $return_arr['errors_arr'][] = $error_arr['display_error'];
                else
                    $return_arr['errors_arr'][] = self::s2p_t( 'Error initializing API object.' );
            } elseif( !$api->do_call( array( 'allow_remote_calls' => false ) ) )
            {
                $return_arr['errors_arr'][] = self::s2p_t( 'API call FAILED. (%ss)', $api->get_call_time() );

                if( ($error_arr = $api->get_error()) and is_array( $error_arr ) )
                    $return_arr['errors_arr'][] = 'API said: '.$error_arr['display_error'];
            } else
            {
                $return_arr['success_arr'][] = self::s2p_t( 'Successfull API call. (%ss)', $api->get_call_time() );

                $finalize_arr = array();
                $finalize_arr['redirect_now'] = false;

                if( !($finalize_result = $api->do_finalize( $finalize_arr )) )
                    $return_arr['warnings_arr'][] = self::s2p_t( 'Error calling finalize on result.' );

                elseif( !empty( $finalize_result['should_redirect'] ) and !empty( $finalize_result['redirect_to'] ) )
                {
                    $return_arr['success_arr'][] = self::s2p_t( 'To finalize this transaction go to: <a href="%s" target="_blank">%s</a>', self::form_str( $finalize_result['redirect_to'] ), $finalize_result['redirect_to'] );
                }

                $return_arr['finalize_result'] = $finalize_result;
            }

            $return_arr['api_obj'] = $api;
        }

        return $return_arr;
    }

    static public function json_display( $str )
    {
        return str_replace( array( ',', '{', '}', '[', ']', "\n\n", "}\n,", "]\n," ), array( ",\n", "{\n", "\n}\n", "[\n", "]\n", "\n", '},', '],' ), $str );
    }

    public function get_form( $params = false )
    {
        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['form_action_suffix'] ) )
            $params['form_action_suffix'] = '';
        if( empty( $params['post_params'] ) )
            $params['post_params'] = self::extract_post_data();
        if( empty( $params['submit_result'] ) )
            $params['submit_result'] = self::default_submit_result();

        if( empty( $params['base_url'] ) )
            $params['base_url'] = $this->base_url();

        if( empty( $params['submit_text'] ) )
            $params['submit_text'] = self::s2p_t( 'Simulate API call' );
        if( empty( $params['form_name'] ) )
            $params['form_name'] = 's2p_demo_form';

        if( empty( $params['base_url'] ) )
        {
            $this->set_error( self::ERR_BASE_URL, self::s2p_t( 'Couldn\'t guess base URL. Please set it manually using %s::base_url( url ) method.', __CLASS__ ) );
            return false;
        }

        $post_params = self::validate_post_data( $params['post_params'] );
        $submit_result = self::validate_submit_result( $params['submit_result'] );

        if( empty( $params['method'] ) )
            $params['method'] = '';
        if( empty( $params['func'] ) )
            $params['func'] = '';

        $method = '';
        if( !empty( $params['method'] ) )
            $method = $params['method'];
        elseif( !empty( $post_params['method'] ) )
            $method = $post_params['method'];

        $func = '';
        if( !empty( $params['func'] ) )
            $func = $params['func'];
        elseif( !empty( $post_params['func'] ) )
            $func = $post_params['func'];

        $post_params['method'] = $method;
        $params['method'] = $method;

        $post_params['func'] = $func;
        $params['func'] = $func;

        if( !empty( $method ) and !$this->init_method( $method ) )
            return false;

        if( !empty( $func ) and !$this->init_functionality( $func ) )
        {
            $func = '';
            $post_params['func'] = $func;
            $params['func'] = $func;
        }

        $form_arr = array();
        $form_arr['form_name'] = $params['form_name'];

        ob_start();
        ?>
        <p><?php echo self::s2p_t( 'Form will be submitted to' )?>: <em><?php echo $params['base_url'].$params['form_action_suffix']?></em><br/>
        <?php echo self::s2p_t( 'If this URL doesn\'t look right you will have to edit the script and set right base URL using $demo->base_url(); call.' )?></p>
        <form name="<?php echo $params['form_name']?>" action="<?php echo $params['base_url'].$params['form_action_suffix']?>" method="post" class="s2p_form">

        <?php
            if( !empty( $submit_result['errors_arr'] ) and is_array( $submit_result['errors_arr'] ) )
            {
                ?><div class="errors_container"><?php
                foreach( $submit_result['errors_arr'] as $key => $error )
                {
                    if( !is_numeric( $key ) )
                        continue;

                    ?><div class="error_text"><?php echo $error?></div><?php
                }

                if( !empty( $submit_result['errors_arr']['gvars'] ) and is_array( $submit_result['errors_arr']['gvars'] ) )
                {
                    foreach( $submit_result['errors_arr']['gvars'] as $key => $error )
                    {
                        ?><div class="error_text"><?php echo 'Error ['.$key.']: '.$error?></div><?php
                    }
                }
                ?></div><?php
            }

            if( !empty( $submit_result['success_arr'] ) and is_array( $submit_result['success_arr'] ) )
            {
                ?><div class="success_container"><?php
                foreach( $submit_result['success_arr'] as $key => $error )
                {
                    if( !is_numeric( $key ) )
                        continue;

                    ?><div class="success_text"><?php echo $error?></div><?php
                }
                ?></div><?php
            }

            if( !empty( $submit_result['warnings_arr'] ) and is_array( $submit_result['warnings_arr'] ) )
            {
                ?><div class="warnings_container"><?php
                foreach( $submit_result['warnings_arr'] as $key => $error )
                {
                    if( !is_numeric( $key ) )
                        continue;

                    ?><div class="warning_text"><?php echo $error?></div><?php
                }
                ?></div><?php
            }

        /** @var S2P_SDK_API $api_obj */
        $api_obj = $submit_result['api_obj']
        ?>

        <div id="api_form" style="width: 100%;<?php echo (!empty( $api_obj )?'display:none;':'');?>">

            <?php
            if( !empty( $api_obj ) )
            {
                ?><div id="s2p_api_result_toggler"><a href="javascript:void(0);" onclick="toggle_container( 'api_form' );toggle_container( 'api_result' );"><?php echo self::s2p_t( 'View API result' )?></a> &raquo;</div><?php
            }
            ?>

            <?php echo $this->get_form_common_fields( $post_params, $form_arr ); ?>

            <?php echo $this->get_form_method_get_params_fields( $post_params, $form_arr ); ?>

            <?php echo $this->get_form_method_parameters_fields( $post_params, $form_arr ); ?>

            <div class="form_field" style="text-align: center;">
                <input type="submit" id="do_submit" name="do_submit" value="<?php echo self::form_str( $params['submit_text'] );?>" />
            </div>
        </div>

        <?php
        if( !empty( $api_obj ) )
        {
            if( !($base_api_obj = $api_obj->get_api_obj())
             or !($call_result = $base_api_obj->get_call_result())
             or !is_array( $call_result ) )
                $call_result = array();

            if( empty( $call_result['request']['request_details']['request_header'] ) )
                $call_result['request']['request_details']['request_header'] = '';
            if( empty( $call_result['request']['request_buffer'] ) )
                $call_result['request']['request_buffer'] = '';
            if( empty( $call_result['request']['response_buffer'] ) )
                $call_result['request']['response_buffer'] = '';

            ?>
            <div id="api_result" style="width: 100%; display:block;">

                <div id="s2p_api_form_toggler">&laquo; <a href="javascript:void(0);" onclick="toggle_container( 'api_form' );toggle_container( 'api_result' );"><?php echo self::s2p_t( 'View API form' )?></a></div>

                <div class="http_headers_code">
                    <div class="http_headers_code_title"><?php echo self::s2p_t( 'Request headers' );?></div>
                    <?php echo nl2br( trim( $call_result['request']['request_details']['request_header'] ) );?>
                </div>

                <div class="http_headers_code">
                    <div class="http_headers_code_title"><a href="javascript:void(0);" onclick="toggle_container( 's2p_api_request_body' )"><?php echo self::s2p_t( 'Request body' );?></a></div>
                    <div id="s2p_api_request_body" style="display: none;">
                        <div id="s2p_api_request_body_raw_toggler">&laquo; <a href="javascript:void(0)" onclick="toggle_container( 's2p_api_request_body_raw' );toggle_container( 's2p_api_request_body_formatted' );"><?php echo self::s2p_t( 'Raw / Formatted response' )?></a> &raquo; </div>
                        <div id="s2p_api_request_body_raw" style="display: block;"><?php echo (empty( $call_result['request']['request_buffer'] )?'(empty)':nl2br( trim( $call_result['request']['request_buffer'] ) ));?></div>
                        <div id="s2p_api_request_body_formatted" style="display: none;"><?php echo (empty( $call_result['request']['request_buffer'] )?'(empty)':nl2br( self::json_display( trim( $call_result['request']['request_buffer'] ) ) ));?></div>
                    </div>
                </div>

                <div class="http_headers_code">
                    <div class="http_headers_code_title"><?php echo self::s2p_t( 'Response headers' );?></div>
                    <?php
                    if( !empty( $call_result['request']['response_headers'] ) and is_array( $call_result['request']['response_headers'] ) )
                    {
                        foreach( $call_result['request']['response_headers'] as $header_key => $header_val )
                        {
                            if( !is_numeric( $header_key ) )
                                echo $header_key.': ';

                            echo $header_val."\n<br/>";
                        }
                    }
                    ?>
                </div>

                <div class="http_headers_code">
                    <div class="http_headers_code_title"><a href="javascript:void(0);" onclick="toggle_container( 's2p_api_response_body' )"><?php echo self::s2p_t( 'Response body' );?></a></div>
                    <div id="s2p_api_response_body" style="display: none;"><?php echo (empty( $call_result['request']['response_buffer'] )?'(empty)':nl2br( trim( $call_result['request']['response_buffer'] ) ));?></div>
                </div>

                <div class="http_headers_code">
                    <div class="http_headers_code_title"><?php echo self::s2p_t( 'Processed response (array)' );?></div>
                    <?php if( empty( $call_result['response']['response_array'] ) )
                            echo '(empty)';
                        else
                        {
                            ob_start();
                            var_dump( $call_result['response']['response_array'] );
                            $buf = ob_get_clean();

                            echo nl2br( str_replace( '  ', ' &nbsp;', $buf ) );
                        }
                    ?>
                </div>

            </div>
            <?php
        }
        ?>

        </form>
        <?php
        $buf = ob_get_clean();

        return $buf;
    }

    public function display_header( $params = false )
    {
        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['form_name'] ) )
            $params['form_name'] = 's2p_demo_form';

        ?><html><head>
<title><?php self::s2p_t( 'SDK demo page' )?></title>
<style>
body { font-family: 'Droid Sans', sans-serif; font-size: 0.8em; }
input, select { font-family: 'Droid Sans', sans-serif; font-size: 1em; }
.clearfix { clear: both; }
.s2p_form { margin: 10px auto; width: 800px; }
#s2p_api_result_toggler { width: 100%; text-align: right; margin-bottom: 10px; clear: both; }
#s2p_api_form_toggler { width: 100%; text-align: left; margin-bottom: 10px; clear: both; }
.s2p_form fieldset { margin-bottom: 5px; }
.form_field { clear: both; width: 100%; padding: 3px; min-height: 30px; margin-bottom: 5px; }
.form_field label { width: 180px; float: left; line-height: 25px; }
.form_field label.mandatory:after { color: red; content: '*'; }
.form_field .form_input { float: left; min-height: 30px; vertical-align: middle; }
.form_field .form_input .form_input_regexp { margin: 0 5px; font-size: small; }
.form_input_regexp_exp { display: none; border: 1px solid gray; margin: 0 5px; border-radius: 5px; background-color: white; padding: 5px; position: absolute; }
.form_input_regexp_exp { cursor: pointer; }
.form_field .form_input input { padding: 3px; }
.form_field .form_input select { padding: 2px; max-width: 300px; }
.form_field .form_input input:not([type='checkbox']) { width: 300px; padding: 3px; border: 1px solid #a1a1a1; }
.form_field .form_input_array { width: 100%; clear: both; margin: 5px; 0; }
.form_field .form_input_array input:not([type='checkbox']) { width: 150px; padding: 3px; border: 1px solid #a1a1a1; }
.form_field .form_input_array select { max-width: 200px; }
.s2p_form .form_input_blob_array { width: 100%; clear: both; margin: 5px; 0; border-bottom: 1px solid #808080; }
.s2p_form .form_input_blob_array input:not([type='checkbox']) { width: 150px; padding: 3px; border: 1px solid #a1a1a1; }
.s2p_form .form_input_blob_array select { max-width: 200px; }
.field_adder_container { clear:both; width:100%; margin-bottom: 5px; }
.s2p_form .errors_container { border: 2px dotted red; width: 100%; padding: 10px; margin: 10px auto; }
.s2p_form .errors_container .error_text { width: 100%; clear: both; }
.s2p_form .success_container { border: 2px dotted green; width: 100%; padding: 10px; margin: 10px auto; }
.s2p_form .success_container .success_text { width: 100%; clear: both; }
.s2p_form .warnings_container { border: 2px dotted #ffff00; width: 100%; padding: 10px; margin: 10px auto; }
.s2p_form .warnings_container .warning_text { width: 100%; clear: both; }
.http_headers_code { padding: 5px; border: 2px solid slategray; font-family: 'Ubuntu Mono', "Courier New", Courier, monospace; width: 100%; clear: both; margin: 5px 0;  }
.http_headers_code .http_headers_code_title { width: 100%; font-family: inherit !important; font-weight: bold; clear: both; }
.datepicker { width: 120px !important; }
#s2p_api_request_body_raw_toggler { margin: 5px 0; }
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link href='http://fonts.googleapis.com/css?family=Ubuntu+Mono:400,400italic,700,700italic|Droid+Sans:400,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>

<script type="text/javascript">
function toggle_container( id )
{
    var obj = $('#'+id);
    if( obj )
    {
        obj.toggle();
    }
}

function toggle_regexp( obj )
{
    if( obj )
    {
        obj.parent().find('.form_input_regexp_exp').toggle().on('click', function(){
            $(this).hide();
        });
    }
}

function add_methods_array_element( template_id )
{
    var template_obj = $('#'+template_id+'___template');
    var container_obj = $('#'+template_id+'___container');

    if( !template_obj || !container_obj )
        return;

    var clone_obj = template_obj.clone();

    clone_obj.prop( 'id', '' );

    // enable all inputs
    clone_obj.find( 'input').prop( 'disabled', false );
    clone_obj.find( 'select').prop( 'disabled', false );
    clone_obj.find( 'textarea').prop( 'disabled', false );

    clone_obj.show();

    clone_obj.appendTo( container_obj );
    //document.<?php echo $params['form_name']?>.submit();
}

function add_methods_blob_array_element( template_id )
{
    var template_obj = $('#mparam_container_'+template_id+'___template');
    var container_obj = $('#mparam_container_'+template_id);

    if( !template_obj || !container_obj )
        return;

    var clone_obj = template_obj.clone();

    clone_obj.prop( 'id', '' );

    // enable all inputs
    clone_obj.find( 'input').prop( 'disabled', false );
    clone_obj.find( 'select').prop( 'disabled', false );
    clone_obj.find( 'textarea').prop( 'disabled', false );

    var container_index = container_obj.children().length;

    clone_obj.find( 'input').each(function() {
        var new_name = $(this).prop( 'name' ).replace( '{*BLOB_ARRAY_INDEX*}', container_index );

        $(this).attr( 'name', new_name );
        $(this).attr( 'id', '' );
    });
    clone_obj.find( 'select').each(function() {
        var new_name = $(this).prop( 'name' ).replace( '{*BLOB_ARRAY_INDEX*}', container_index );

        $(this).attr( 'name', new_name );
        $(this).attr( 'id', '' );
    });
    clone_obj.find( 'textarea').each(function() {
        var new_name = $(this).prop( 'name' ).replace( '{*BLOB_ARRAY_INDEX*}', container_index );

        $(this).attr( 'name', new_name );
        $(this).attr( 'id', '' );
    });

    clone_obj.show();

    clone_obj.appendTo( container_obj );
    document.<?php echo $params['form_name']?>.submit();
}

function remove_methods_array_element( a_element, template_id )
{
    if( !a_element || !a_element.parent() || a_element.parent().attr( 'id' ) == template_id + '___template' )
        return;

    a_element.parent().remove();
    //document.<?php echo $params['form_name']?>.submit();
}

function remove_methods_blob_array_element( a_element, template_id )
{
    if( !a_element || !a_element.parent() || a_element.parent().attr( 'id' ) == 'mparam_container_' + template_id + '___template' )
        return;

    a_element.parent().remove();
    document.<?php echo $params['form_name']?>.submit();
}

$(function(){
    $('.datepicker').datepicker({
        firstDay: 1,
        dateFormat: 'yymmdd000000'
    });

    $("[id*='___template'] input, [id*='___template'] select, [id*='___template'] textarea").each(function(){
        if( $(this).attr('id') != 'undefined' )
            $(this).attr( 'id', '' );
    });

    var disabler_obj = $('.input_disabler_container');
    if( disabler_obj )
    {
        disabler_obj.find('input').prop('disabled', true);
        disabler_obj.find('select').prop('disabled', true);
        disabler_obj.find('textarea').prop('disabled', true);
    }
});
</script>


</head>
<body>
<h1><?php echo self::s2p_t( 'Welcome to Smart2Pay SDK demo page!' )?></h1>
<p><?php echo self::s2p_t( 'Please note that this page contains technical information which is intended to help developers start using our SDK.' )?></p>
<small class="clearfix"><?php echo self::s2p_t( 'SDK version' )?>: <?php echo S2P_SDK_VERSION?></small>
<?php
    }

    public function display_footer( $params = false )
    {
        ?></body>
</html><?php
    }

}