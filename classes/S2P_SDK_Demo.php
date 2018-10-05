<?php

namespace S2P_SDK;

class S2P_SDK_Demo extends S2P_SDK_Module
{
    const ALLOW_REMOTE_CALLS = true;

    const ERR_BASE_URL = 1, ERR_INSTANTIATE_METHOD = 2, ERR_FUNCTIONALITY = 3, ERR_APIKEY = 4, ERR_ENVIRONMENT = 5, ERR_FORM_MANDATORY = 6;

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

        $protocol = 'http';
        if( !empty( $_SERVER['HTTPS'] )
        and ($_SERVER['HTTPS'] == 'yes' or $_SERVER['HTTPS'] == 'on' or $_SERVER['HTTPS'] == true or $_SERVER['HTTPS'] == 1) )
            $protocol = 'https';

        $url = $protocol.'://'.$_SERVER['HTTP_HOST'];

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
            'site_id' => 0,
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

        if( empty( $form_arr['hidden_form'] ) )
            $form_arr['hidden_form'] = false;
        else
            $form_arr['hidden_form'] = (empty( $form_arr['hidden_form'] )?false:true);

        if( empty( $post_arr['foobar'] ) )
        {
            $api_config_arr = self::get_api_configuration();

            // form defaults
            if( empty( $post_arr['api_key'] ) and !empty( $api_config_arr['api_key'] ) )
                $post_arr['api_key'] = $api_config_arr['api_key'];
            if( empty( $post_arr['site_id'] ) and !empty( $api_config_arr['site_id'] ) )
                $post_arr['site_id'] = $api_config_arr['site_id'];
            if( empty( $post_arr['environment'] ) and !empty( $api_config_arr['environment'] ) )
                $post_arr['environment'] = $api_config_arr['environment'];
        }

        ob_start();

        if( !empty( $form_arr['hidden_form'] ) )
        {
            ?>
            <input type="hidden" id="site_id" name="site_id" value="<?php echo self::form_str( $post_arr['site_id'] )?>" />
            <input type="hidden" id="api_key" name="api_key" value="<?php echo self::form_str( $post_arr['api_key'] )?>" />
            <input type="hidden" id="environment" name="environment" value="<?php echo self::form_str( $post_arr['environment'] )?>" />
            <input type="hidden" id="method" name="method" value="<?php echo self::form_str( $post_arr['method'] )?>" />
            <?php
        } else
        {
            ?>
            <div class="form_field">
                <label for="api_key"><?php echo self::s2p_t( 'API Key' )?></label>

                <div class="form_input">
                    <input type="text" id="api_key" name="api_key" value="<?php echo self::form_str( $post_arr['api_key'] )?>" style="width: 350px;" />
                </div>
            </div>

            <div class="form_field">
                <label for="site_id"><?php echo self::s2p_t( 'Site ID' )?></label>

                <div class="form_input">
                    <input type="text" id="site_id" name="site_id" value="<?php echo self::form_str( $post_arr['site_id'] )?>" style="width: 150px;" />
                </div>
            </div>

            <div class="form_field">
                <label for="environment"><?php echo self::s2p_t( 'Environment' )?></label>

                <div class="form_input"><select id="environment" name="environment">
                    <option value="test" <?php echo( $post_arr['environment'] == 'test' ? 'selected="selected"' : '' )?>>Test</option>
                    <option value="live" <?php echo( $post_arr['environment'] == 'live' ? 'selected="selected"' : '' )?>>Live</option>
                </select></div>
            </div>

            <div class="form_field">
                <label for="method"><?php echo self::s2p_t( 'Method' )?></label>

                <div class="form_input">
                    <select id="method" name="method" onchange="document.<?php echo $form_arr['form_name']?>.submit()">
                        <option value=""> - <?php echo self::s2p_t( 'Choose an option' );?> -</option><?php
                        if( ( $all_methods = S2P_SDK_Method::get_all_methods() )
                        and is_array( $all_methods ) )
                        {
                            foreach( $all_methods as $method_id => $method_details )
                            {
                                if( empty( $method_details['instance'] ) )
                                    continue;

                                /** @var S2P_SDK_Method $instance */
                                $instance = $method_details['instance'];

                                ?><option value="<?php echo $method_id?>" <?php echo( $post_arr['method'] == $method_id ? 'selected="selected"' : '' )?>><?php echo $method_id . ' - ' . $instance->get_name()?></option><?php
                            }
                        }
                    ?></select></div>
            </div>

        <?php
        }

        if( !empty( $this->_method )
        and ($method_functionalities = $this->_method->get_functionalities())
        and is_array( $method_functionalities ) )
        {
            if( !empty( $form_arr['hidden_form'] ) )
            {
                ?>
                <input type="hidden" id="func" name="func" value="<?php echo self::form_str( $post_arr['func'] )?>" />
                <?php
            } else
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
                    $submit_result_arr['errors_arr']['gvars'][ $get_var['name'] ] = self::s2p_t( 'Mandatory field %s not provided.', (!empty( $get_var['display_name'] )?$get_var['display_name']:$get_var['name']) );

                continue;
            }

            if( !empty( $get_var['regexp'] )
            and !@preg_match( '/'.$get_var['regexp'].'/', $post_arr['gvars'][$get_var['name']] ) )
            {
                $submit_result_arr['errors_arr']['gvars'][ $get_var['name'] ] = self::s2p_t( 'Field %s failed regular expression %s.', (!empty( $get_var['display_name'] )?$get_var['display_name']:$get_var['name']), $get_var['regexp'] );
                continue;
            }

            $var_value = S2P_SDK_Scope_Variable::scalar_value( $get_var['type'], $post_arr['gvars'][$get_var['name']], $get_var['array_type'], $get_var['array_numeric_keys'] );
            $default_var_value = null;
            if( array_key_exists( 'default', $get_var ) )
                $default_var_value = S2P_SDK_Scope_Variable::scalar_value( $get_var['type'], $get_var['default'], $get_var['array_type'], $get_var['array_numeric_keys'] );

            if( !empty( $get_var['skip_if_default'] )
            and ($var_value === null or $var_value === $default_var_value) )
                continue;

            if( !empty( $get_var['value_source'] ) and $value_source_obj::valid_type( $get_var['value_source'] ) )
            {
                $value_source_obj->source_type( $get_var['value_source'] );
                if( !$value_source_obj->valid_value( $var_value ) )
                {
                    $submit_result_arr['errors_arr']['gvars'][ $get_var['name'] ] = self::s2p_t( 'Variable %s contains invalid value [%s].', (!empty( $get_var['display_name'] )?$get_var['display_name']:$get_var['name']), $var_value );
                    continue;
                }
            } elseif( !empty( $get_var['value_array'] ) and is_array( $get_var['value_array'] )
                  and !isset( $get_var['value_array'][$var_value] ) )
            {
                $submit_result_arr['errors_arr']['gvars'][ $get_var['name'] ] = self::s2p_t( 'Variable %s contains invalid value [%s].', (!empty( $get_var['display_name'] )?$get_var['display_name']:$get_var['name']), $var_value );
                continue;
            }
        }

        return $submit_result_arr;
    }

    public function get_form_method_get_params_fields_input( $get_var, $params = false )
    {
        if( !($get_var = S2P_SDK_Method::validate_get_variable_definition( $get_var )) )
        {
            self::st_reset_error();
            return '';
        }

        if( empty( $params ) or !is_array( $params ) )
            $params = array();
        if( empty( $params['post_arr'] ) or !is_array( $params['post_arr'] ) )
            $params['post_arr'] = array();
        if( !isset( $params['allow_remote_calls'] ) )
            $params['allow_remote_calls'] = self::ALLOW_REMOTE_CALLS;
        else
            $params['allow_remote_calls'] = (!empty( $params['allow_remote_calls'] )?true:false);

        $post_arr = $params['post_arr'];

        ob_start();
        $field_name = 'gvars['.$get_var['name'].']';
        $field_id = 'gvar_'.$get_var['name'];

        if( isset( $post_arr['gvars'][$get_var['name']] ) )
            $field_value = $post_arr['gvars'][$get_var['name']];
        elseif( !empty( $get_var['check_constant'] ) and defined( $get_var['check_constant'] ) )
            $field_value = constant( $get_var['check_constant'] );
        elseif( array_key_exists( 'default', $get_var ) )
            $field_value = $get_var['default'];
        else
            $field_value = '';

        if( !($field_type_arr = S2P_SDK_Scope_Variable::valid_type( $get_var['type'] )) )
            $field_type_arr = array( 'title' => '[undefined]' );

        ?>
        <div class="form_field">
            <label for="<?php echo $field_id?>" class="<?php echo (!empty( $get_var['mandatory'] )?'mandatory':'')?>"><?php echo (!empty( $get_var['display_name'] )?$get_var['display_name'].' ('.$get_var['name'].')':$get_var['name'])?></label>
            <div class="form_input"><?php

                if( empty( $get_var['value_source'] )
                 or !S2P_SDK_Values_Source::valid_type( $get_var['value_source'] )
                 or !($value_source_obj = new S2P_SDK_Values_Source( $get_var['value_source'] ))
                 // make sure we don't stop here...
                 or ($value_source_obj->remote_calls( $params['allow_remote_calls'] ) and false)
                 or !($options_value = $value_source_obj->get_option_values())
                 or !is_array( $options_value ) )
                    $options_value = array();

                if( empty( $options_value )
                and !empty( $get_var['value_array'] ) and is_array( $get_var['value_array'] ) )
                    $options_value = $get_var['value_array'];

                if( !empty( $options_value ) and is_array( $options_value ) )
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
                        if( false )
                        {
                            ?>
                            <input type="checkbox" id="<?php echo $field_id ?>" name="<?php echo $field_name ?>" value="1" <?php echo(! empty($field_value) ? 'checked="checked"' : '') ?> /><?php
                        }

                        ?><select id="<?php echo $field_id ?>" name="<?php echo $field_name ?>">
                        <option value="null"><?php echo self::s2p_t( 'Don\'t send' )?></option><option value="true"><?php echo self::s2p_t( 'True' )?></option><option value="false"><?php echo self::s2p_t( 'False' )?></option>
                        </select><?php

                    } elseif( $get_var['type'] == S2P_SDK_Scope_Variable::TYPE_DATETIME )
                    {
                        ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" class="datepicker" /><?php
                    } elseif( $get_var['type'] == S2P_SDK_Scope_Variable::TYPE_DATE )
                    {
                        ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" class="datepickerdateonly" /><?php
                    } else
                    {
                        ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" /><?php
                    }

                    echo ' ('.$field_type_arr['title'].')';
                }

                if( $get_var['type'] == S2P_SDK_Scope_Variable::TYPE_DATETIME )
                {
                    echo ' - yyyymmddhhmmss';
                } elseif( $get_var['type'] == S2P_SDK_Scope_Variable::TYPE_DATE )
                {
                    echo ' - yyyymmdd';
                }
            ?></div>
        </div>
        <?php
        $buf = ob_get_clean();

        return $buf;
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
        $input_params_arr = array();
        $input_params_arr['post_arr'] = $post_arr;
        $input_params_arr['allow_remote_calls'] = self::ALLOW_REMOTE_CALLS;

        foreach( $func_details['get_variables'] as $get_var )
        {
            echo $this->get_form_method_get_params_fields_input( $get_var, $input_params_arr );
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
                    if( $post_arr['keys'][$key] === ''
                     or $post_arr['keys'][$key] === null )
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

    public function get_form_method_parameters_fields_detailed( $structure_definition, $mandatory_arr, $hide_keys_arr, $post_arr, $form_arr, $params = false )
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
                <label for="<?php echo $field_id?>" title="<?php echo (!empty( $structure_definition['display_name'] )?$structure_definition['display_name'].' - ':'').self::form_str( $params['path'] )?>" class="<?php echo (!empty( $field_mandatory )?'mandatory':'')?>"><?php echo $structure_definition['name']?></label>
                <div class="form_input"><?php

                    if( empty( $structure_definition['value_source'] )
                     or !S2P_SDK_Values_Source::valid_type( $structure_definition['value_source'] )
                     or !($value_source_obj = new S2P_SDK_Values_Source( $structure_definition['value_source'] ))
                     // make sure we don't stop here...
                     or ($value_source_obj->remote_calls( self::ALLOW_REMOTE_CALLS ) and false)
                     or !($options_value = $value_source_obj->get_option_values())
                     or !is_array( $options_value ) )
                        $options_value = array();

                    if( empty( $options_value )
                    and !empty( $structure_definition['value_array'] ) and is_array( $structure_definition['value_array'] ) )
                        $options_value = $structure_definition['value_array'];

                    if( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_BOOL )
                    {
                        if( is_string( $field_value ) )
                        {
                            if( $field_value == 'true' )
                                $field_value = true;
                            elseif( $field_value == 'false' )
                                $field_value = false;
                            elseif( $field_value == 'null' )
                                $field_value = null;
                        }

                        if( false )
                        {
                            ?>
                            <input type="checkbox" id="<?php echo $field_id ?>" name="<?php echo $field_name ?>" value="1" <?php echo(! empty($field_value) ? 'checked="checked"' : '') ?> /><?php
                        }

                        ?><select id="<?php echo $field_id ?>" name="<?php echo $field_name ?>">
                        <option value="null"><?php echo self::s2p_t( 'Don\'t send' )?></option><option value="true"><?php echo self::s2p_t( 'True' )?></option><option value="false"><?php echo self::s2p_t( 'False' )?></option>
                    </select><?php
                    } elseif( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_DATETIME )
                    {
                        ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" class="datepicker" /><?php
                    } elseif( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_DATE )
                    {
                        ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" class="datepickerdateonly" /><?php
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
                    } elseif( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_DATE )
                    {
                        echo ' - yyyymmdd';
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

        ob_start();
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

    public function clear_empty_post_fields( $post_vars_arr )
    {
        if( empty( $post_vars_arr ) or !is_array( $post_vars_arr ) )
            return array();

        $new_post_arr = array();
        foreach( $post_vars_arr as $key => $val )
        {
            if( is_array( $val ) )
            {
                if( ($new_val = $this->clear_empty_post_fields( $val )) )
                    $new_post_arr[$key] = $new_val;

                continue;
            }

            if( $val === ''
             or $val === 'null' )
                continue;

            $new_post_arr[$key] = $val;
        }

        return $new_post_arr;
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

        if( !($mparams_arr = $this->clear_empty_post_fields( $post_arr['mparams'] )) )
            $mparams_arr = array();

        $post_arr['mparams'] = $mparams_arr;

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

        if( empty( $post_arr['site_id'] ) )
            $return_arr['errors_arr'][] = self::s2p_t( 'Invalid Site ID.' );

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
            $api_params['site_id'] = $post_arr['site_id'];
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

        if( empty( $params['hidden_form'] ) )
            $params['hidden_form'] = false;
        else
            $params['hidden_form'] = (empty( $params['hidden_form'] )?false:true);

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
        if( empty( $params['form_id'] ) )
        {
            if( !empty( $params['form_name'] ) )
                $params['form_id'] = $params['form_name'];
            else
                $params['form_id'] = 's2p_demo_form_'.microtime( true );
        }

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

        if( !empty( $params['hidden_form'] )
        and (empty( $method ) or empty( $func )) )
        {
            $this->set_error( self::ERR_FORM_MANDATORY, self::s2p_t( 'Please provide method and functionality for this form.' ) );
            return false;
        }

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
        $form_arr['hidden_form'] = $params['hidden_form'];

        ob_start();

        if( empty( $params['hidden_form'] ) )
        {
            ?>
            <p class="s2p-alert"><?php echo self::s2p_t( 'Form will be submitted to' )?>:
                <span class="post_url"><?php echo $params['base_url'] . $params['form_action_suffix']?></span><br/>
                <?php echo self::s2p_t( 'If this URL doesn\'t look right you will have to edit the script and set right base URL using $demo->base_url(); call.' )?>
            </p>
            <?php
        }

        ?>
        <form name="<?php echo $params['form_name']?>" id="<?php echo $params['form_id']?>" action="<?php echo $params['base_url'].$params['form_action_suffix']?>" method="post" class="s2p_form">
        <input type="hidden" name="foobar" value="1"/>
        <?php

        ob_start();
        $form_messages = array();
        $form_messages['errors_arr'] = array();
        $form_messages['success_arr'] = array();
        $form_messages['warnings_arr'] = array();
        if( !empty( $submit_result['errors_arr'] ) and is_array( $submit_result['errors_arr'] ) )
        {
            ?><div class="s2p-error"><?php
            foreach( $submit_result['errors_arr'] as $key => $error )
            {
                if( !is_numeric( $key ) )
                    continue;

                ?><div class="error_text"><?php echo $error?></div><?php
                $form_messages['errors_arr'][] = $error;
            }

            if( !empty( $submit_result['errors_arr']['gvars'] ) and is_array( $submit_result['errors_arr']['gvars'] ) )
            {
                foreach( $submit_result['errors_arr']['gvars'] as $key => $error )
                {
                    ?><div class="error_text"><?php echo 'Error [' . $key . ']: ' . $error?></div><?php
                    $form_messages['errors_arr'][] = 'Error on '.$key.': '.$error;
                }
            }
            ?></div><?php
        }

        if( !empty( $submit_result['success_arr'] ) and is_array( $submit_result['success_arr'] ) )
        {
            ?><div class="s2p-success"><?php
            foreach( $submit_result['success_arr'] as $key => $error )
            {
                if( !is_numeric( $key ) )
                    continue;

                ?><div class="success_text"><?php echo $error?></div><?php
                $form_messages['success_arr'][] = $error;
            }
            ?></div><?php
        }

        if( !empty( $submit_result['warnings_arr'] ) and is_array( $submit_result['warnings_arr'] ) )
        {
            ?><div class="s2p-warn"><?php
            foreach( $submit_result['warnings_arr'] as $key => $error )
            {
                if( !is_numeric( $key ) )
                    continue;

                ?><div class="warning_text"><?php echo $error?></div><?php
                $form_messages['warnings_arr'][] = $error;
            }
            ?></div><?php
        }

        if( !empty( $params['hidden_form'] ) )
            ob_end_clean();
        else
        {
            ob_end_flush();
            $form_messages = false;
        }

        /** @var S2P_SDK_API $api_obj */
        $api_obj = $submit_result['api_obj'];


		if( !empty( $api_obj ) )
		{
			?>
			<ul class="nav nav-tabs" id="s2p_api_result_toggler">
				<li>
					<a href="javascript:void(0);" onclick="if (!$(this).parent().hasClass('active')) { $(this).parent().parent().children('li').removeClass('active');$(this).parent().toggleClass('active'); toggle_container( 'api_form' );toggle_container( 'api_result' ); }"><?php echo self::s2p_t( 'View API form' )?></a>
				</li>
				<li class="active">
					<a href="javascript:void(0);" onclick="if (!$(this).parent().hasClass('active')) { $(this).parent().parent().children('li').removeClass('active');$(this).parent().toggleClass('active'); toggle_container( 'api_form' );toggle_container( 'api_result' ); }"><?php echo self::s2p_t( 'View API result' )?></a>
				</li>
			</ul>
			<?php
		}		

        if( empty( $params['hidden_form'] ) )
        {
            ?><div id="api_form" style="width: 100%;<?php echo( !empty( $api_obj ) ? 'display:none;' : '' );?>"><?php
        }
        ?>

        <?php echo $this->get_form_common_fields( $post_params, $form_arr ); ?>

        <?php echo $this->get_form_method_get_params_fields( $post_params, $form_arr ); ?>

        <?php echo $this->get_form_method_parameters_fields( $post_params, $form_arr ); ?>

        <?php
        if( !empty( $params['hidden_form'] ) )
        {
            ?><input type="hidden" id="do_submit" name="do_submit" value="1" /><?php
        } else
        {
            ?>
            <div class="form_field submit_container" style="text-align: center;">
                <input type="submit" id="do_submit" name="do_submit" value="<?php echo self::form_str( $params['submit_text'] );?>" class="btn btn-primary"/>
            </div>
            </div>
            <?php
        }

        $call_result = false;
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


            if( empty( $params['hidden_form'] ) )
            {
                ?>
                <li id="api_result" class="active" style="width: 100%; display:block;">


					<div class="request">
						<div class="http_headers_code request_headers">
							<div class="http_headers_code_title expanded">
								<a href="javascript:void(0);" onclick="toggle_container( 's2p_api_request_header', $(this) )"><?php echo self::s2p_t( 'Request headers' );?></a>
							</div>
							<pre id="s2p_api_request_header"><code class="http"><?php echo trim( $call_result['request']['request_details']['request_header'] );?></code></pre>
						</div>

						<div class="http_headers_code request_body">
							<div class="http_headers_code_title">
								<a href="javascript:void(0);" onclick="toggle_container( 's2p_api_request_body', $(this) )"><?php echo self::s2p_t( 'Request body' );?></a>
							</div>
							<div id="s2p_api_request_body" style="display: none;">
								<div id="s2p_api_request_body_raw_toggler">&laquo;
									<a href="javascript:void(0)" onclick="toggleResponseFormat($(this)); toggle_container( 's2p_api_request_body_raw' );toggle_container( 's2p_api_request_body_formatted' );"><?php echo self::s2p_t( '<span class="active">Raw</span> / <span>Formatted request<span>' )?></a> &raquo;
								</div>
								<div id="s2p_api_request_body_raw" style="display: block;">
									<pre><code class=""><?php echo( empty( $call_result['request']['request_buffer'] ) ? '(empty)' : nl2br( trim( $call_result['request']['request_buffer'] ) ) );?></pre></code>
								</div>
								<div id="s2p_api_request_body_formatted" style="display: none;">
									<pre><code class="json"><?php echo( empty( $call_result['request']['request_buffer'] ) ? '(empty)' : nl2br( self::json_display( trim( $call_result['request']['request_buffer'] ) ) ) );?></pre></code>
								</div>
							</div>
						</div>
					</div>

					<div class="response">
						<div class="http_headers_code response_headers">
							<div class="http_headers_code_title expanded">
								<a href="javascript:void(0);" onclick="toggle_container( 's2p_api_response_header', $(this) )"><?php echo self::s2p_t( 'Response headers' );?></a>
							</div>
							
							<pre id="s2p_api_response_header"><code class="http"><?php
								if( !empty( $call_result['request']['response_headers'] ) and is_array( $call_result['request']['response_headers'] ) )
								{
									foreach( $call_result['request']['response_headers'] as $header_key => $header_val )
									{
										if( !is_numeric( $header_key ) )
											echo $header_key . ': ';

										echo $header_val . "\n";
									}
								}
							?></code></pre>
							
						</div>
					
						<div class="http_headers_code response_body">
							<div class="http_headers_code_title">
								<a href="javascript:void(0);" onclick="toggle_container( 's2p_api_response_body', $(this) )"><?php echo self::s2p_t( 'Response body' );?></a>
							</div>
							<div id="s2p_api_response_body" style="display: none;">
								<pre><code class="json"><?php echo( empty( $call_result['request']['response_buffer'] ) ? '(empty)' : nl2br( trim( $call_result['request']['response_buffer'] ) ) );?></pre></code>
							</div>
						</div>

						<div class="http_headers_code processed_response">
							<div class="http_headers_code_title expanded">
								<a href="javascript:void(0);" onclick="toggle_container( 's2p_api_processed_response', $(this) )"><?php echo self::s2p_t( 'Processed response (array)' );?></a>
							</div>
							<pre id="s2p_api_processed_response"><code class="json"><?php if( empty( $call_result['response']['response_array'] ) )
								echo '(empty)';
							else
							{
								ob_start();
								var_dump( $call_result['response']['response_array'] );
								$buf = ob_get_clean();

								echo nl2br( str_replace( '  ', ' &nbsp;', $buf ) );
							}
							?></code></pre>
						</div>
					
					</div>

                </li>
				</ul>
            <?php
            }
        }

        ?></form><?php

        $buf = ob_get_clean();

        $return_arr = array();
        $return_arr['form_id'] = $params['form_id'];
        $return_arr['form_name'] = $params['form_name'];
        $return_arr['form_messages'] = $form_messages;
        $return_arr['buffer'] = $buf;
        $return_arr['call_result'] = $call_result;

        return $return_arr;
    }

    public function display_header( $params = false )
    {
        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['form_name'] ) )
            $params['form_name'] = 's2p_demo_form';

        ?><html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php self::s2p_t( 'SDK demo page' )?></title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/redmond/jquery-ui.css">
	<script>
		/*! jQuery UI - v1.11.4 - 2015-11-01
		* http://jqueryui.com
		* Includes: core.js, datepicker.js
		* Copyright jQuery Foundation and other contributors; Licensed MIT */

		(function(e){"function"==typeof define&&define.amd?define(["jquery"],e):e(jQuery)})(function(e){function t(t,s){var a,n,r,o=t.nodeName.toLowerCase();return"area"===o?(a=t.parentNode,n=a.name,t.href&&n&&"map"===a.nodeName.toLowerCase()?(r=e("img[usemap='#"+n+"']")[0],!!r&&i(r)):!1):(/^(input|select|textarea|button|object)$/.test(o)?!t.disabled:"a"===o?t.href||s:s)&&i(t)}function i(t){return e.expr.filters.visible(t)&&!e(t).parents().addBack().filter(function(){return"hidden"===e.css(this,"visibility")}).length}function s(e){for(var t,i;e.length&&e[0]!==document;){if(t=e.css("position"),("absolute"===t||"relative"===t||"fixed"===t)&&(i=parseInt(e.css("zIndex"),10),!isNaN(i)&&0!==i))return i;e=e.parent()}return 0}function a(){this._curInst=null,this._keyEvent=!1,this._disabledInputs=[],this._datepickerShowing=!1,this._inDialog=!1,this._mainDivId="ui-datepicker-div",this._inlineClass="ui-datepicker-inline",this._appendClass="ui-datepicker-append",this._triggerClass="ui-datepicker-trigger",this._dialogClass="ui-datepicker-dialog",this._disableClass="ui-datepicker-disabled",this._unselectableClass="ui-datepicker-unselectable",this._currentClass="ui-datepicker-current-day",this._dayOverClass="ui-datepicker-days-cell-over",this.regional=[],this.regional[""]={closeText:"Done",prevText:"Prev",nextText:"Next",currentText:"Today",monthNames:["January","February","March","April","May","June","July","August","September","October","November","December"],monthNamesShort:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],dayNames:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],dayNamesShort:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],dayNamesMin:["Su","Mo","Tu","We","Th","Fr","Sa"],weekHeader:"Wk",dateFormat:"mm/dd/yy",firstDay:0,isRTL:!1,showMonthAfterYear:!1,yearSuffix:""},this._defaults={showOn:"focus",showAnim:"fadeIn",showOptions:{},defaultDate:null,appendText:"",buttonText:"...",buttonImage:"",buttonImageOnly:!1,hideIfNoPrevNext:!1,navigationAsDateFormat:!1,gotoCurrent:!1,changeMonth:!1,changeYear:!1,yearRange:"c-10:c+10",showOtherMonths:!1,selectOtherMonths:!1,showWeek:!1,calculateWeek:this.iso8601Week,shortYearCutoff:"+10",minDate:null,maxDate:null,duration:"fast",beforeShowDay:null,beforeShow:null,onSelect:null,onChangeMonthYear:null,onClose:null,numberOfMonths:1,showCurrentAtPos:0,stepMonths:1,stepBigMonths:12,altField:"",altFormat:"",constrainInput:!0,showButtonPanel:!1,autoSize:!1,disabled:!1},e.extend(this._defaults,this.regional[""]),this.regional.en=e.extend(!0,{},this.regional[""]),this.regional["en-US"]=e.extend(!0,{},this.regional.en),this.dpDiv=n(e("<div id='"+this._mainDivId+"' class='ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all'></div>"))}function n(t){var i="button, .ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-calendar td a";return t.delegate(i,"mouseout",function(){e(this).removeClass("ui-state-hover"),-1!==this.className.indexOf("ui-datepicker-prev")&&e(this).removeClass("ui-datepicker-prev-hover"),-1!==this.className.indexOf("ui-datepicker-next")&&e(this).removeClass("ui-datepicker-next-hover")}).delegate(i,"mouseover",r)}function r(){e.datepicker._isDisabledDatepicker(h.inline?h.dpDiv.parent()[0]:h.input[0])||(e(this).parents(".ui-datepicker-calendar").find("a").removeClass("ui-state-hover"),e(this).addClass("ui-state-hover"),-1!==this.className.indexOf("ui-datepicker-prev")&&e(this).addClass("ui-datepicker-prev-hover"),-1!==this.className.indexOf("ui-datepicker-next")&&e(this).addClass("ui-datepicker-next-hover"))}function o(t,i){e.extend(t,i);for(var s in i)null==i[s]&&(t[s]=i[s]);return t}e.ui=e.ui||{},e.extend(e.ui,{version:"1.11.4",keyCode:{BACKSPACE:8,COMMA:188,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,LEFT:37,PAGE_DOWN:34,PAGE_UP:33,PERIOD:190,RIGHT:39,SPACE:32,TAB:9,UP:38}}),e.fn.extend({scrollParent:function(t){var i=this.css("position"),s="absolute"===i,a=t?/(auto|scroll|hidden)/:/(auto|scroll)/,n=this.parents().filter(function(){var t=e(this);return s&&"static"===t.css("position")?!1:a.test(t.css("overflow")+t.css("overflow-y")+t.css("overflow-x"))}).eq(0);return"fixed"!==i&&n.length?n:e(this[0].ownerDocument||document)},uniqueId:function(){var e=0;return function(){return this.each(function(){this.id||(this.id="ui-id-"+ ++e)})}}(),removeUniqueId:function(){return this.each(function(){/^ui-id-\d+$/.test(this.id)&&e(this).removeAttr("id")})}}),e.extend(e.expr[":"],{data:e.expr.createPseudo?e.expr.createPseudo(function(t){return function(i){return!!e.data(i,t)}}):function(t,i,s){return!!e.data(t,s[3])},focusable:function(i){return t(i,!isNaN(e.attr(i,"tabindex")))},tabbable:function(i){var s=e.attr(i,"tabindex"),a=isNaN(s);return(a||s>=0)&&t(i,!a)}}),e("<a>").outerWidth(1).jquery||e.each(["Width","Height"],function(t,i){function s(t,i,s,n){return e.each(a,function(){i-=parseFloat(e.css(t,"padding"+this))||0,s&&(i-=parseFloat(e.css(t,"border"+this+"Width"))||0),n&&(i-=parseFloat(e.css(t,"margin"+this))||0)}),i}var a="Width"===i?["Left","Right"]:["Top","Bottom"],n=i.toLowerCase(),r={innerWidth:e.fn.innerWidth,innerHeight:e.fn.innerHeight,outerWidth:e.fn.outerWidth,outerHeight:e.fn.outerHeight};e.fn["inner"+i]=function(t){return void 0===t?r["inner"+i].call(this):this.each(function(){e(this).css(n,s(this,t)+"px")})},e.fn["outer"+i]=function(t,a){return"number"!=typeof t?r["outer"+i].call(this,t):this.each(function(){e(this).css(n,s(this,t,!0,a)+"px")})}}),e.fn.addBack||(e.fn.addBack=function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}),e("<a>").data("a-b","a").removeData("a-b").data("a-b")&&(e.fn.removeData=function(t){return function(i){return arguments.length?t.call(this,e.camelCase(i)):t.call(this)}}(e.fn.removeData)),e.ui.ie=!!/msie [\w.]+/.exec(navigator.userAgent.toLowerCase()),e.fn.extend({focus:function(t){return function(i,s){return"number"==typeof i?this.each(function(){var t=this;setTimeout(function(){e(t).focus(),s&&s.call(t)},i)}):t.apply(this,arguments)}}(e.fn.focus),disableSelection:function(){var e="onselectstart"in document.createElement("div")?"selectstart":"mousedown";return function(){return this.bind(e+".ui-disableSelection",function(e){e.preventDefault()})}}(),enableSelection:function(){return this.unbind(".ui-disableSelection")},zIndex:function(t){if(void 0!==t)return this.css("zIndex",t);if(this.length)for(var i,s,a=e(this[0]);a.length&&a[0]!==document;){if(i=a.css("position"),("absolute"===i||"relative"===i||"fixed"===i)&&(s=parseInt(a.css("zIndex"),10),!isNaN(s)&&0!==s))return s;a=a.parent()}return 0}}),e.ui.plugin={add:function(t,i,s){var a,n=e.ui[t].prototype;for(a in s)n.plugins[a]=n.plugins[a]||[],n.plugins[a].push([i,s[a]])},call:function(e,t,i,s){var a,n=e.plugins[t];if(n&&(s||e.element[0].parentNode&&11!==e.element[0].parentNode.nodeType))for(a=0;n.length>a;a++)e.options[n[a][0]]&&n[a][1].apply(e.element,i)}},e.extend(e.ui,{datepicker:{version:"1.11.4"}});var h;e.extend(a.prototype,{markerClassName:"hasDatepicker",maxRows:4,_widgetDatepicker:function(){return this.dpDiv},setDefaults:function(e){return o(this._defaults,e||{}),this},_attachDatepicker:function(t,i){var s,a,n;s=t.nodeName.toLowerCase(),a="div"===s||"span"===s,t.id||(this.uuid+=1,t.id="dp"+this.uuid),n=this._newInst(e(t),a),n.settings=e.extend({},i||{}),"input"===s?this._connectDatepicker(t,n):a&&this._inlineDatepicker(t,n)},_newInst:function(t,i){var s=t[0].id.replace(/([^A-Za-z0-9_\-])/g,"\\\\$1");return{id:s,input:t,selectedDay:0,selectedMonth:0,selectedYear:0,drawMonth:0,drawYear:0,inline:i,dpDiv:i?n(e("<div class='"+this._inlineClass+" ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all'></div>")):this.dpDiv}},_connectDatepicker:function(t,i){var s=e(t);i.append=e([]),i.trigger=e([]),s.hasClass(this.markerClassName)||(this._attachments(s,i),s.addClass(this.markerClassName).keydown(this._doKeyDown).keypress(this._doKeyPress).keyup(this._doKeyUp),this._autoSize(i),e.data(t,"datepicker",i),i.settings.disabled&&this._disableDatepicker(t))},_attachments:function(t,i){var s,a,n,r=this._get(i,"appendText"),o=this._get(i,"isRTL");i.append&&i.append.remove(),r&&(i.append=e("<span class='"+this._appendClass+"'>"+r+"</span>"),t[o?"before":"after"](i.append)),t.unbind("focus",this._showDatepicker),i.trigger&&i.trigger.remove(),s=this._get(i,"showOn"),("focus"===s||"both"===s)&&t.focus(this._showDatepicker),("button"===s||"both"===s)&&(a=this._get(i,"buttonText"),n=this._get(i,"buttonImage"),i.trigger=e(this._get(i,"buttonImageOnly")?e("<img/>").addClass(this._triggerClass).attr({src:n,alt:a,title:a}):e("<button type='button'></button>").addClass(this._triggerClass).html(n?e("<img/>").attr({src:n,alt:a,title:a}):a)),t[o?"before":"after"](i.trigger),i.trigger.click(function(){return e.datepicker._datepickerShowing&&e.datepicker._lastInput===t[0]?e.datepicker._hideDatepicker():e.datepicker._datepickerShowing&&e.datepicker._lastInput!==t[0]?(e.datepicker._hideDatepicker(),e.datepicker._showDatepicker(t[0])):e.datepicker._showDatepicker(t[0]),!1}))},_autoSize:function(e){if(this._get(e,"autoSize")&&!e.inline){var t,i,s,a,n=new Date(2009,11,20),r=this._get(e,"dateFormat");r.match(/[DM]/)&&(t=function(e){for(i=0,s=0,a=0;e.length>a;a++)e[a].length>i&&(i=e[a].length,s=a);return s},n.setMonth(t(this._get(e,r.match(/MM/)?"monthNames":"monthNamesShort"))),n.setDate(t(this._get(e,r.match(/DD/)?"dayNames":"dayNamesShort"))+20-n.getDay())),e.input.attr("size",this._formatDate(e,n).length)}},_inlineDatepicker:function(t,i){var s=e(t);s.hasClass(this.markerClassName)||(s.addClass(this.markerClassName).append(i.dpDiv),e.data(t,"datepicker",i),this._setDate(i,this._getDefaultDate(i),!0),this._updateDatepicker(i),this._updateAlternate(i),i.settings.disabled&&this._disableDatepicker(t),i.dpDiv.css("display","block"))},_dialogDatepicker:function(t,i,s,a,n){var r,h,l,u,d,c=this._dialogInst;return c||(this.uuid+=1,r="dp"+this.uuid,this._dialogInput=e("<input type='text' id='"+r+"' style='position: absolute; top: -100px; width: 0px;'/>"),this._dialogInput.keydown(this._doKeyDown),e("body").append(this._dialogInput),c=this._dialogInst=this._newInst(this._dialogInput,!1),c.settings={},e.data(this._dialogInput[0],"datepicker",c)),o(c.settings,a||{}),i=i&&i.constructor===Date?this._formatDate(c,i):i,this._dialogInput.val(i),this._pos=n?n.length?n:[n.pageX,n.pageY]:null,this._pos||(h=document.documentElement.clientWidth,l=document.documentElement.clientHeight,u=document.documentElement.scrollLeft||document.body.scrollLeft,d=document.documentElement.scrollTop||document.body.scrollTop,this._pos=[h/2-100+u,l/2-150+d]),this._dialogInput.css("left",this._pos[0]+20+"px").css("top",this._pos[1]+"px"),c.settings.onSelect=s,this._inDialog=!0,this.dpDiv.addClass(this._dialogClass),this._showDatepicker(this._dialogInput[0]),e.blockUI&&e.blockUI(this.dpDiv),e.data(this._dialogInput[0],"datepicker",c),this},_destroyDatepicker:function(t){var i,s=e(t),a=e.data(t,"datepicker");s.hasClass(this.markerClassName)&&(i=t.nodeName.toLowerCase(),e.removeData(t,"datepicker"),"input"===i?(a.append.remove(),a.trigger.remove(),s.removeClass(this.markerClassName).unbind("focus",this._showDatepicker).unbind("keydown",this._doKeyDown).unbind("keypress",this._doKeyPress).unbind("keyup",this._doKeyUp)):("div"===i||"span"===i)&&s.removeClass(this.markerClassName).empty(),h===a&&(h=null))},_enableDatepicker:function(t){var i,s,a=e(t),n=e.data(t,"datepicker");a.hasClass(this.markerClassName)&&(i=t.nodeName.toLowerCase(),"input"===i?(t.disabled=!1,n.trigger.filter("button").each(function(){this.disabled=!1}).end().filter("img").css({opacity:"1.0",cursor:""})):("div"===i||"span"===i)&&(s=a.children("."+this._inlineClass),s.children().removeClass("ui-state-disabled"),s.find("select.ui-datepicker-month, select.ui-datepicker-year").prop("disabled",!1)),this._disabledInputs=e.map(this._disabledInputs,function(e){return e===t?null:e}))},_disableDatepicker:function(t){var i,s,a=e(t),n=e.data(t,"datepicker");a.hasClass(this.markerClassName)&&(i=t.nodeName.toLowerCase(),"input"===i?(t.disabled=!0,n.trigger.filter("button").each(function(){this.disabled=!0}).end().filter("img").css({opacity:"0.5",cursor:"default"})):("div"===i||"span"===i)&&(s=a.children("."+this._inlineClass),s.children().addClass("ui-state-disabled"),s.find("select.ui-datepicker-month, select.ui-datepicker-year").prop("disabled",!0)),this._disabledInputs=e.map(this._disabledInputs,function(e){return e===t?null:e}),this._disabledInputs[this._disabledInputs.length]=t)},_isDisabledDatepicker:function(e){if(!e)return!1;for(var t=0;this._disabledInputs.length>t;t++)if(this._disabledInputs[t]===e)return!0;return!1},_getInst:function(t){try{return e.data(t,"datepicker")}catch(i){throw"Missing instance data for this datepicker"}},_optionDatepicker:function(t,i,s){var a,n,r,h,l=this._getInst(t);return 2===arguments.length&&"string"==typeof i?"defaults"===i?e.extend({},e.datepicker._defaults):l?"all"===i?e.extend({},l.settings):this._get(l,i):null:(a=i||{},"string"==typeof i&&(a={},a[i]=s),l&&(this._curInst===l&&this._hideDatepicker(),n=this._getDateDatepicker(t,!0),r=this._getMinMaxDate(l,"min"),h=this._getMinMaxDate(l,"max"),o(l.settings,a),null!==r&&void 0!==a.dateFormat&&void 0===a.minDate&&(l.settings.minDate=this._formatDate(l,r)),null!==h&&void 0!==a.dateFormat&&void 0===a.maxDate&&(l.settings.maxDate=this._formatDate(l,h)),"disabled"in a&&(a.disabled?this._disableDatepicker(t):this._enableDatepicker(t)),this._attachments(e(t),l),this._autoSize(l),this._setDate(l,n),this._updateAlternate(l),this._updateDatepicker(l)),void 0)},_changeDatepicker:function(e,t,i){this._optionDatepicker(e,t,i)},_refreshDatepicker:function(e){var t=this._getInst(e);t&&this._updateDatepicker(t)},_setDateDatepicker:function(e,t){var i=this._getInst(e);i&&(this._setDate(i,t),this._updateDatepicker(i),this._updateAlternate(i))},_getDateDatepicker:function(e,t){var i=this._getInst(e);return i&&!i.inline&&this._setDateFromField(i,t),i?this._getDate(i):null},_doKeyDown:function(t){var i,s,a,n=e.datepicker._getInst(t.target),r=!0,o=n.dpDiv.is(".ui-datepicker-rtl");if(n._keyEvent=!0,e.datepicker._datepickerShowing)switch(t.keyCode){case 9:e.datepicker._hideDatepicker(),r=!1;break;case 13:return a=e("td."+e.datepicker._dayOverClass+":not(."+e.datepicker._currentClass+")",n.dpDiv),a[0]&&e.datepicker._selectDay(t.target,n.selectedMonth,n.selectedYear,a[0]),i=e.datepicker._get(n,"onSelect"),i?(s=e.datepicker._formatDate(n),i.apply(n.input?n.input[0]:null,[s,n])):e.datepicker._hideDatepicker(),!1;case 27:e.datepicker._hideDatepicker();break;case 33:e.datepicker._adjustDate(t.target,t.ctrlKey?-e.datepicker._get(n,"stepBigMonths"):-e.datepicker._get(n,"stepMonths"),"M");break;case 34:e.datepicker._adjustDate(t.target,t.ctrlKey?+e.datepicker._get(n,"stepBigMonths"):+e.datepicker._get(n,"stepMonths"),"M");break;case 35:(t.ctrlKey||t.metaKey)&&e.datepicker._clearDate(t.target),r=t.ctrlKey||t.metaKey;break;case 36:(t.ctrlKey||t.metaKey)&&e.datepicker._gotoToday(t.target),r=t.ctrlKey||t.metaKey;break;case 37:(t.ctrlKey||t.metaKey)&&e.datepicker._adjustDate(t.target,o?1:-1,"D"),r=t.ctrlKey||t.metaKey,t.originalEvent.altKey&&e.datepicker._adjustDate(t.target,t.ctrlKey?-e.datepicker._get(n,"stepBigMonths"):-e.datepicker._get(n,"stepMonths"),"M");break;case 38:(t.ctrlKey||t.metaKey)&&e.datepicker._adjustDate(t.target,-7,"D"),r=t.ctrlKey||t.metaKey;break;case 39:(t.ctrlKey||t.metaKey)&&e.datepicker._adjustDate(t.target,o?-1:1,"D"),r=t.ctrlKey||t.metaKey,t.originalEvent.altKey&&e.datepicker._adjustDate(t.target,t.ctrlKey?+e.datepicker._get(n,"stepBigMonths"):+e.datepicker._get(n,"stepMonths"),"M");break;case 40:(t.ctrlKey||t.metaKey)&&e.datepicker._adjustDate(t.target,7,"D"),r=t.ctrlKey||t.metaKey;break;default:r=!1}else 36===t.keyCode&&t.ctrlKey?e.datepicker._showDatepicker(this):r=!1;r&&(t.preventDefault(),t.stopPropagation())},_doKeyPress:function(t){var i,s,a=e.datepicker._getInst(t.target);return e.datepicker._get(a,"constrainInput")?(i=e.datepicker._possibleChars(e.datepicker._get(a,"dateFormat")),s=String.fromCharCode(null==t.charCode?t.keyCode:t.charCode),t.ctrlKey||t.metaKey||" ">s||!i||i.indexOf(s)>-1):void 0},_doKeyUp:function(t){var i,s=e.datepicker._getInst(t.target);if(s.input.val()!==s.lastVal)try{i=e.datepicker.parseDate(e.datepicker._get(s,"dateFormat"),s.input?s.input.val():null,e.datepicker._getFormatConfig(s)),i&&(e.datepicker._setDateFromField(s),e.datepicker._updateAlternate(s),e.datepicker._updateDatepicker(s))}catch(a){}return!0},_showDatepicker:function(t){if(t=t.target||t,"input"!==t.nodeName.toLowerCase()&&(t=e("input",t.parentNode)[0]),!e.datepicker._isDisabledDatepicker(t)&&e.datepicker._lastInput!==t){var i,a,n,r,h,l,u;i=e.datepicker._getInst(t),e.datepicker._curInst&&e.datepicker._curInst!==i&&(e.datepicker._curInst.dpDiv.stop(!0,!0),i&&e.datepicker._datepickerShowing&&e.datepicker._hideDatepicker(e.datepicker._curInst.input[0])),a=e.datepicker._get(i,"beforeShow"),n=a?a.apply(t,[t,i]):{},n!==!1&&(o(i.settings,n),i.lastVal=null,e.datepicker._lastInput=t,e.datepicker._setDateFromField(i),e.datepicker._inDialog&&(t.value=""),e.datepicker._pos||(e.datepicker._pos=e.datepicker._findPos(t),e.datepicker._pos[1]+=t.offsetHeight),r=!1,e(t).parents().each(function(){return r|="fixed"===e(this).css("position"),!r}),h={left:e.datepicker._pos[0],top:e.datepicker._pos[1]},e.datepicker._pos=null,i.dpDiv.empty(),i.dpDiv.css({position:"absolute",display:"block",top:"-1000px"}),e.datepicker._updateDatepicker(i),h=e.datepicker._checkOffset(i,h,r),i.dpDiv.css({position:e.datepicker._inDialog&&e.blockUI?"static":r?"fixed":"absolute",display:"none",left:h.left+"px",top:h.top+"px"}),i.inline||(l=e.datepicker._get(i,"showAnim"),u=e.datepicker._get(i,"duration"),i.dpDiv.css("z-index",s(e(t))+1),e.datepicker._datepickerShowing=!0,e.effects&&e.effects.effect[l]?i.dpDiv.show(l,e.datepicker._get(i,"showOptions"),u):i.dpDiv[l||"show"](l?u:null),e.datepicker._shouldFocusInput(i)&&i.input.focus(),e.datepicker._curInst=i))}},_updateDatepicker:function(t){this.maxRows=4,h=t,t.dpDiv.empty().append(this._generateHTML(t)),this._attachHandlers(t);var i,s=this._getNumberOfMonths(t),a=s[1],n=17,o=t.dpDiv.find("."+this._dayOverClass+" a");o.length>0&&r.apply(o.get(0)),t.dpDiv.removeClass("ui-datepicker-multi-2 ui-datepicker-multi-3 ui-datepicker-multi-4").width(""),a>1&&t.dpDiv.addClass("ui-datepicker-multi-"+a).css("width",n*a+"em"),t.dpDiv[(1!==s[0]||1!==s[1]?"add":"remove")+"Class"]("ui-datepicker-multi"),t.dpDiv[(this._get(t,"isRTL")?"add":"remove")+"Class"]("ui-datepicker-rtl"),t===e.datepicker._curInst&&e.datepicker._datepickerShowing&&e.datepicker._shouldFocusInput(t)&&t.input.focus(),t.yearshtml&&(i=t.yearshtml,setTimeout(function(){i===t.yearshtml&&t.yearshtml&&t.dpDiv.find("select.ui-datepicker-year:first").replaceWith(t.yearshtml),i=t.yearshtml=null},0))},_shouldFocusInput:function(e){return e.input&&e.input.is(":visible")&&!e.input.is(":disabled")&&!e.input.is(":focus")},_checkOffset:function(t,i,s){var a=t.dpDiv.outerWidth(),n=t.dpDiv.outerHeight(),r=t.input?t.input.outerWidth():0,o=t.input?t.input.outerHeight():0,h=document.documentElement.clientWidth+(s?0:e(document).scrollLeft()),l=document.documentElement.clientHeight+(s?0:e(document).scrollTop());return i.left-=this._get(t,"isRTL")?a-r:0,i.left-=s&&i.left===t.input.offset().left?e(document).scrollLeft():0,i.top-=s&&i.top===t.input.offset().top+o?e(document).scrollTop():0,i.left-=Math.min(i.left,i.left+a>h&&h>a?Math.abs(i.left+a-h):0),i.top-=Math.min(i.top,i.top+n>l&&l>n?Math.abs(n+o):0),i},_findPos:function(t){for(var i,s=this._getInst(t),a=this._get(s,"isRTL");t&&("hidden"===t.type||1!==t.nodeType||e.expr.filters.hidden(t));)t=t[a?"previousSibling":"nextSibling"];return i=e(t).offset(),[i.left,i.top]},_hideDatepicker:function(t){var i,s,a,n,r=this._curInst;!r||t&&r!==e.data(t,"datepicker")||this._datepickerShowing&&(i=this._get(r,"showAnim"),s=this._get(r,"duration"),a=function(){e.datepicker._tidyDialog(r)},e.effects&&(e.effects.effect[i]||e.effects[i])?r.dpDiv.hide(i,e.datepicker._get(r,"showOptions"),s,a):r.dpDiv["slideDown"===i?"slideUp":"fadeIn"===i?"fadeOut":"hide"](i?s:null,a),i||a(),this._datepickerShowing=!1,n=this._get(r,"onClose"),n&&n.apply(r.input?r.input[0]:null,[r.input?r.input.val():"",r]),this._lastInput=null,this._inDialog&&(this._dialogInput.css({position:"absolute",left:"0",top:"-100px"}),e.blockUI&&(e.unblockUI(),e("body").append(this.dpDiv))),this._inDialog=!1)},_tidyDialog:function(e){e.dpDiv.removeClass(this._dialogClass).unbind(".ui-datepicker-calendar")},_checkExternalClick:function(t){if(e.datepicker._curInst){var i=e(t.target),s=e.datepicker._getInst(i[0]);(i[0].id!==e.datepicker._mainDivId&&0===i.parents("#"+e.datepicker._mainDivId).length&&!i.hasClass(e.datepicker.markerClassName)&&!i.closest("."+e.datepicker._triggerClass).length&&e.datepicker._datepickerShowing&&(!e.datepicker._inDialog||!e.blockUI)||i.hasClass(e.datepicker.markerClassName)&&e.datepicker._curInst!==s)&&e.datepicker._hideDatepicker()}},_adjustDate:function(t,i,s){var a=e(t),n=this._getInst(a[0]);this._isDisabledDatepicker(a[0])||(this._adjustInstDate(n,i+("M"===s?this._get(n,"showCurrentAtPos"):0),s),this._updateDatepicker(n))},_gotoToday:function(t){var i,s=e(t),a=this._getInst(s[0]);this._get(a,"gotoCurrent")&&a.currentDay?(a.selectedDay=a.currentDay,a.drawMonth=a.selectedMonth=a.currentMonth,a.drawYear=a.selectedYear=a.currentYear):(i=new Date,a.selectedDay=i.getDate(),a.drawMonth=a.selectedMonth=i.getMonth(),a.drawYear=a.selectedYear=i.getFullYear()),this._notifyChange(a),this._adjustDate(s)},_selectMonthYear:function(t,i,s){var a=e(t),n=this._getInst(a[0]);n["selected"+("M"===s?"Month":"Year")]=n["draw"+("M"===s?"Month":"Year")]=parseInt(i.options[i.selectedIndex].value,10),this._notifyChange(n),this._adjustDate(a)},_selectDay:function(t,i,s,a){var n,r=e(t);e(a).hasClass(this._unselectableClass)||this._isDisabledDatepicker(r[0])||(n=this._getInst(r[0]),n.selectedDay=n.currentDay=e("a",a).html(),n.selectedMonth=n.currentMonth=i,n.selectedYear=n.currentYear=s,this._selectDate(t,this._formatDate(n,n.currentDay,n.currentMonth,n.currentYear)))},_clearDate:function(t){var i=e(t);this._selectDate(i,"")},_selectDate:function(t,i){var s,a=e(t),n=this._getInst(a[0]);i=null!=i?i:this._formatDate(n),n.input&&n.input.val(i),this._updateAlternate(n),s=this._get(n,"onSelect"),s?s.apply(n.input?n.input[0]:null,[i,n]):n.input&&n.input.trigger("change"),n.inline?this._updateDatepicker(n):(this._hideDatepicker(),this._lastInput=n.input[0],"object"!=typeof n.input[0]&&n.input.focus(),this._lastInput=null)},_updateAlternate:function(t){var i,s,a,n=this._get(t,"altField");n&&(i=this._get(t,"altFormat")||this._get(t,"dateFormat"),s=this._getDate(t),a=this.formatDate(i,s,this._getFormatConfig(t)),e(n).each(function(){e(this).val(a)}))},noWeekends:function(e){var t=e.getDay();return[t>0&&6>t,""]},iso8601Week:function(e){var t,i=new Date(e.getTime());return i.setDate(i.getDate()+4-(i.getDay()||7)),t=i.getTime(),i.setMonth(0),i.setDate(1),Math.floor(Math.round((t-i)/864e5)/7)+1},parseDate:function(t,i,s){if(null==t||null==i)throw"Invalid arguments";if(i="object"==typeof i?""+i:i+"",""===i)return null;var a,n,r,o,h=0,l=(s?s.shortYearCutoff:null)||this._defaults.shortYearCutoff,u="string"!=typeof l?l:(new Date).getFullYear()%100+parseInt(l,10),d=(s?s.dayNamesShort:null)||this._defaults.dayNamesShort,c=(s?s.dayNames:null)||this._defaults.dayNames,p=(s?s.monthNamesShort:null)||this._defaults.monthNamesShort,m=(s?s.monthNames:null)||this._defaults.monthNames,f=-1,g=-1,v=-1,y=-1,b=!1,_=function(e){var i=t.length>a+1&&t.charAt(a+1)===e;return i&&a++,i},x=function(e){var t=_(e),s="@"===e?14:"!"===e?20:"y"===e&&t?4:"o"===e?3:2,a="y"===e?s:1,n=RegExp("^\\d{"+a+","+s+"}"),r=i.substring(h).match(n);if(!r)throw"Missing number at position "+h;return h+=r[0].length,parseInt(r[0],10)},k=function(t,s,a){var n=-1,r=e.map(_(t)?a:s,function(e,t){return[[t,e]]}).sort(function(e,t){return-(e[1].length-t[1].length)});if(e.each(r,function(e,t){var s=t[1];return i.substr(h,s.length).toLowerCase()===s.toLowerCase()?(n=t[0],h+=s.length,!1):void 0}),-1!==n)return n+1;throw"Unknown name at position "+h},w=function(){if(i.charAt(h)!==t.charAt(a))throw"Unexpected literal at position "+h;h++};for(a=0;t.length>a;a++)if(b)"'"!==t.charAt(a)||_("'")?w():b=!1;else switch(t.charAt(a)){case"d":v=x("d");break;case"D":k("D",d,c);break;case"o":y=x("o");break;case"m":g=x("m");break;case"M":g=k("M",p,m);break;case"y":f=x("y");break;case"@":o=new Date(x("@")),f=o.getFullYear(),g=o.getMonth()+1,v=o.getDate();break;case"!":o=new Date((x("!")-this._ticksTo1970)/1e4),f=o.getFullYear(),g=o.getMonth()+1,v=o.getDate();break;case"'":_("'")?w():b=!0;break;default:w()}if(i.length>h&&(r=i.substr(h),!/^\s+/.test(r)))throw"Extra/unparsed characters found in date: "+r;if(-1===f?f=(new Date).getFullYear():100>f&&(f+=(new Date).getFullYear()-(new Date).getFullYear()%100+(u>=f?0:-100)),y>-1)for(g=1,v=y;;){if(n=this._getDaysInMonth(f,g-1),n>=v)break;g++,v-=n}if(o=this._daylightSavingAdjust(new Date(f,g-1,v)),o.getFullYear()!==f||o.getMonth()+1!==g||o.getDate()!==v)throw"Invalid date";return o},ATOM:"yy-mm-dd",COOKIE:"D, dd M yy",ISO_8601:"yy-mm-dd",RFC_822:"D, d M y",RFC_850:"DD, dd-M-y",RFC_1036:"D, d M y",RFC_1123:"D, d M yy",RFC_2822:"D, d M yy",RSS:"D, d M y",TICKS:"!",TIMESTAMP:"@",W3C:"yy-mm-dd",_ticksTo1970:1e7*60*60*24*(718685+Math.floor(492.5)-Math.floor(19.7)+Math.floor(4.925)),formatDate:function(e,t,i){if(!t)return"";var s,a=(i?i.dayNamesShort:null)||this._defaults.dayNamesShort,n=(i?i.dayNames:null)||this._defaults.dayNames,r=(i?i.monthNamesShort:null)||this._defaults.monthNamesShort,o=(i?i.monthNames:null)||this._defaults.monthNames,h=function(t){var i=e.length>s+1&&e.charAt(s+1)===t;return i&&s++,i},l=function(e,t,i){var s=""+t;if(h(e))for(;i>s.length;)s="0"+s;return s},u=function(e,t,i,s){return h(e)?s[t]:i[t]},d="",c=!1;if(t)for(s=0;e.length>s;s++)if(c)"'"!==e.charAt(s)||h("'")?d+=e.charAt(s):c=!1;else switch(e.charAt(s)){case"d":d+=l("d",t.getDate(),2);break;case"D":d+=u("D",t.getDay(),a,n);break;case"o":d+=l("o",Math.round((new Date(t.getFullYear(),t.getMonth(),t.getDate()).getTime()-new Date(t.getFullYear(),0,0).getTime())/864e5),3);break;case"m":d+=l("m",t.getMonth()+1,2);break;case"M":d+=u("M",t.getMonth(),r,o);break;case"y":d+=h("y")?t.getFullYear():(10>t.getYear()%100?"0":"")+t.getYear()%100;break;case"@":d+=t.getTime();break;case"!":d+=1e4*t.getTime()+this._ticksTo1970;break;case"'":h("'")?d+="'":c=!0;break;default:d+=e.charAt(s)}return d},_possibleChars:function(e){var t,i="",s=!1,a=function(i){var s=e.length>t+1&&e.charAt(t+1)===i;return s&&t++,s};for(t=0;e.length>t;t++)if(s)"'"!==e.charAt(t)||a("'")?i+=e.charAt(t):s=!1;else switch(e.charAt(t)){case"d":case"m":case"y":case"@":i+="0123456789";break;case"D":case"M":return null;case"'":a("'")?i+="'":s=!0;break;default:i+=e.charAt(t)}return i},_get:function(e,t){return void 0!==e.settings[t]?e.settings[t]:this._defaults[t]},_setDateFromField:function(e,t){if(e.input.val()!==e.lastVal){var i=this._get(e,"dateFormat"),s=e.lastVal=e.input?e.input.val():null,a=this._getDefaultDate(e),n=a,r=this._getFormatConfig(e);try{n=this.parseDate(i,s,r)||a}catch(o){s=t?"":s}e.selectedDay=n.getDate(),e.drawMonth=e.selectedMonth=n.getMonth(),e.drawYear=e.selectedYear=n.getFullYear(),e.currentDay=s?n.getDate():0,e.currentMonth=s?n.getMonth():0,e.currentYear=s?n.getFullYear():0,this._adjustInstDate(e)}},_getDefaultDate:function(e){return this._restrictMinMax(e,this._determineDate(e,this._get(e,"defaultDate"),new Date))},_determineDate:function(t,i,s){var a=function(e){var t=new Date;return t.setDate(t.getDate()+e),t},n=function(i){try{return e.datepicker.parseDate(e.datepicker._get(t,"dateFormat"),i,e.datepicker._getFormatConfig(t))}catch(s){}for(var a=(i.toLowerCase().match(/^c/)?e.datepicker._getDate(t):null)||new Date,n=a.getFullYear(),r=a.getMonth(),o=a.getDate(),h=/([+\-]?[0-9]+)\s*(d|D|w|W|m|M|y|Y)?/g,l=h.exec(i);l;){switch(l[2]||"d"){case"d":case"D":o+=parseInt(l[1],10);break;case"w":case"W":o+=7*parseInt(l[1],10);break;case"m":case"M":r+=parseInt(l[1],10),o=Math.min(o,e.datepicker._getDaysInMonth(n,r));break;case"y":case"Y":n+=parseInt(l[1],10),o=Math.min(o,e.datepicker._getDaysInMonth(n,r))}l=h.exec(i)}return new Date(n,r,o)},r=null==i||""===i?s:"string"==typeof i?n(i):"number"==typeof i?isNaN(i)?s:a(i):new Date(i.getTime());return r=r&&"Invalid Date"==""+r?s:r,r&&(r.setHours(0),r.setMinutes(0),r.setSeconds(0),r.setMilliseconds(0)),this._daylightSavingAdjust(r)},_daylightSavingAdjust:function(e){return e?(e.setHours(e.getHours()>12?e.getHours()+2:0),e):null},_setDate:function(e,t,i){var s=!t,a=e.selectedMonth,n=e.selectedYear,r=this._restrictMinMax(e,this._determineDate(e,t,new Date));e.selectedDay=e.currentDay=r.getDate(),e.drawMonth=e.selectedMonth=e.currentMonth=r.getMonth(),e.drawYear=e.selectedYear=e.currentYear=r.getFullYear(),a===e.selectedMonth&&n===e.selectedYear||i||this._notifyChange(e),this._adjustInstDate(e),e.input&&e.input.val(s?"":this._formatDate(e))},_getDate:function(e){var t=!e.currentYear||e.input&&""===e.input.val()?null:this._daylightSavingAdjust(new Date(e.currentYear,e.currentMonth,e.currentDay));return t},_attachHandlers:function(t){var i=this._get(t,"stepMonths"),s="#"+t.id.replace(/\\\\/g,"\\");t.dpDiv.find("[data-handler]").map(function(){var t={prev:function(){e.datepicker._adjustDate(s,-i,"M")},next:function(){e.datepicker._adjustDate(s,+i,"M")},hide:function(){e.datepicker._hideDatepicker()},today:function(){e.datepicker._gotoToday(s)},selectDay:function(){return e.datepicker._selectDay(s,+this.getAttribute("data-month"),+this.getAttribute("data-year"),this),!1},selectMonth:function(){return e.datepicker._selectMonthYear(s,this,"M"),!1},selectYear:function(){return e.datepicker._selectMonthYear(s,this,"Y"),!1}};e(this).bind(this.getAttribute("data-event"),t[this.getAttribute("data-handler")])})},_generateHTML:function(e){var t,i,s,a,n,r,o,h,l,u,d,c,p,m,f,g,v,y,b,_,x,k,w,T,S,D,M,N,C,A,P,I,F,H,z,j,E,L,O,W=new Date,R=this._daylightSavingAdjust(new Date(W.getFullYear(),W.getMonth(),W.getDate())),Y=this._get(e,"isRTL"),J=this._get(e,"showButtonPanel"),B=this._get(e,"hideIfNoPrevNext"),K=this._get(e,"navigationAsDateFormat"),Q=this._getNumberOfMonths(e),V=this._get(e,"showCurrentAtPos"),U=this._get(e,"stepMonths"),q=1!==Q[0]||1!==Q[1],G=this._daylightSavingAdjust(e.currentDay?new Date(e.currentYear,e.currentMonth,e.currentDay):new Date(9999,9,9)),$=this._getMinMaxDate(e,"min"),X=this._getMinMaxDate(e,"max"),Z=e.drawMonth-V,et=e.drawYear;if(0>Z&&(Z+=12,et--),X)for(t=this._daylightSavingAdjust(new Date(X.getFullYear(),X.getMonth()-Q[0]*Q[1]+1,X.getDate())),t=$&&$>t?$:t;this._daylightSavingAdjust(new Date(et,Z,1))>t;)Z--,0>Z&&(Z=11,et--);for(e.drawMonth=Z,e.drawYear=et,i=this._get(e,"prevText"),i=K?this.formatDate(i,this._daylightSavingAdjust(new Date(et,Z-U,1)),this._getFormatConfig(e)):i,s=this._canAdjustMonth(e,-1,et,Z)?"<a class='ui-datepicker-prev ui-corner-all' data-handler='prev' data-event='click' title='"+i+"'><span class='ui-icon ui-icon-circle-triangle-"+(Y?"e":"w")+"'>"+i+"</span></a>":B?"":"<a class='ui-datepicker-prev ui-corner-all ui-state-disabled' title='"+i+"'><span class='ui-icon ui-icon-circle-triangle-"+(Y?"e":"w")+"'>"+i+"</span></a>",a=this._get(e,"nextText"),a=K?this.formatDate(a,this._daylightSavingAdjust(new Date(et,Z+U,1)),this._getFormatConfig(e)):a,n=this._canAdjustMonth(e,1,et,Z)?"<a class='ui-datepicker-next ui-corner-all' data-handler='next' data-event='click' title='"+a+"'><span class='ui-icon ui-icon-circle-triangle-"+(Y?"w":"e")+"'>"+a+"</span></a>":B?"":"<a class='ui-datepicker-next ui-corner-all ui-state-disabled' title='"+a+"'><span class='ui-icon ui-icon-circle-triangle-"+(Y?"w":"e")+"'>"+a+"</span></a>",r=this._get(e,"currentText"),o=this._get(e,"gotoCurrent")&&e.currentDay?G:R,r=K?this.formatDate(r,o,this._getFormatConfig(e)):r,h=e.inline?"":"<button type='button' class='ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all' data-handler='hide' data-event='click'>"+this._get(e,"closeText")+"</button>",l=J?"<div class='ui-datepicker-buttonpane ui-widget-content'>"+(Y?h:"")+(this._isInRange(e,o)?"<button type='button' class='ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all' data-handler='today' data-event='click'>"+r+"</button>":"")+(Y?"":h)+"</div>":"",u=parseInt(this._get(e,"firstDay"),10),u=isNaN(u)?0:u,d=this._get(e,"showWeek"),c=this._get(e,"dayNames"),p=this._get(e,"dayNamesMin"),m=this._get(e,"monthNames"),f=this._get(e,"monthNamesShort"),g=this._get(e,"beforeShowDay"),v=this._get(e,"showOtherMonths"),y=this._get(e,"selectOtherMonths"),b=this._getDefaultDate(e),_="",k=0;Q[0]>k;k++){for(w="",this.maxRows=4,T=0;Q[1]>T;T++){if(S=this._daylightSavingAdjust(new Date(et,Z,e.selectedDay)),D=" ui-corner-all",M="",q){if(M+="<div class='ui-datepicker-group",Q[1]>1)switch(T){case 0:M+=" ui-datepicker-group-first",D=" ui-corner-"+(Y?"right":"left");
		break;case Q[1]-1:M+=" ui-datepicker-group-last",D=" ui-corner-"+(Y?"left":"right");break;default:M+=" ui-datepicker-group-middle",D=""}M+="'>"}for(M+="<div class='ui-datepicker-header ui-widget-header ui-helper-clearfix"+D+"'>"+(/all|left/.test(D)&&0===k?Y?n:s:"")+(/all|right/.test(D)&&0===k?Y?s:n:"")+this._generateMonthYearHeader(e,Z,et,$,X,k>0||T>0,m,f)+"</div><table class='ui-datepicker-calendar'><thead>"+"<tr>",N=d?"<th class='ui-datepicker-week-col'>"+this._get(e,"weekHeader")+"</th>":"",x=0;7>x;x++)C=(x+u)%7,N+="<th scope='col'"+((x+u+6)%7>=5?" class='ui-datepicker-week-end'":"")+">"+"<span title='"+c[C]+"'>"+p[C]+"</span></th>";for(M+=N+"</tr></thead><tbody>",A=this._getDaysInMonth(et,Z),et===e.selectedYear&&Z===e.selectedMonth&&(e.selectedDay=Math.min(e.selectedDay,A)),P=(this._getFirstDayOfMonth(et,Z)-u+7)%7,I=Math.ceil((P+A)/7),F=q?this.maxRows>I?this.maxRows:I:I,this.maxRows=F,H=this._daylightSavingAdjust(new Date(et,Z,1-P)),z=0;F>z;z++){for(M+="<tr>",j=d?"<td class='ui-datepicker-week-col'>"+this._get(e,"calculateWeek")(H)+"</td>":"",x=0;7>x;x++)E=g?g.apply(e.input?e.input[0]:null,[H]):[!0,""],L=H.getMonth()!==Z,O=L&&!y||!E[0]||$&&$>H||X&&H>X,j+="<td class='"+((x+u+6)%7>=5?" ui-datepicker-week-end":"")+(L?" ui-datepicker-other-month":"")+(H.getTime()===S.getTime()&&Z===e.selectedMonth&&e._keyEvent||b.getTime()===H.getTime()&&b.getTime()===S.getTime()?" "+this._dayOverClass:"")+(O?" "+this._unselectableClass+" ui-state-disabled":"")+(L&&!v?"":" "+E[1]+(H.getTime()===G.getTime()?" "+this._currentClass:"")+(H.getTime()===R.getTime()?" ui-datepicker-today":""))+"'"+(L&&!v||!E[2]?"":" title='"+E[2].replace(/'/g,"&#39;")+"'")+(O?"":" data-handler='selectDay' data-event='click' data-month='"+H.getMonth()+"' data-year='"+H.getFullYear()+"'")+">"+(L&&!v?"&#xa0;":O?"<span class='ui-state-default'>"+H.getDate()+"</span>":"<a class='ui-state-default"+(H.getTime()===R.getTime()?" ui-state-highlight":"")+(H.getTime()===G.getTime()?" ui-state-active":"")+(L?" ui-priority-secondary":"")+"' href='#'>"+H.getDate()+"</a>")+"</td>",H.setDate(H.getDate()+1),H=this._daylightSavingAdjust(H);M+=j+"</tr>"}Z++,Z>11&&(Z=0,et++),M+="</tbody></table>"+(q?"</div>"+(Q[0]>0&&T===Q[1]-1?"<div class='ui-datepicker-row-break'></div>":""):""),w+=M}_+=w}return _+=l,e._keyEvent=!1,_},_generateMonthYearHeader:function(e,t,i,s,a,n,r,o){var h,l,u,d,c,p,m,f,g=this._get(e,"changeMonth"),v=this._get(e,"changeYear"),y=this._get(e,"showMonthAfterYear"),b="<div class='ui-datepicker-title'>",_="";if(n||!g)_+="<span class='ui-datepicker-month'>"+r[t]+"</span>";else{for(h=s&&s.getFullYear()===i,l=a&&a.getFullYear()===i,_+="<select class='ui-datepicker-month' data-handler='selectMonth' data-event='change'>",u=0;12>u;u++)(!h||u>=s.getMonth())&&(!l||a.getMonth()>=u)&&(_+="<option value='"+u+"'"+(u===t?" selected='selected'":"")+">"+o[u]+"</option>");_+="</select>"}if(y||(b+=_+(!n&&g&&v?"":"&#xa0;")),!e.yearshtml)if(e.yearshtml="",n||!v)b+="<span class='ui-datepicker-year'>"+i+"</span>";else{for(d=this._get(e,"yearRange").split(":"),c=(new Date).getFullYear(),p=function(e){var t=e.match(/c[+\-].*/)?i+parseInt(e.substring(1),10):e.match(/[+\-].*/)?c+parseInt(e,10):parseInt(e,10);return isNaN(t)?c:t},m=p(d[0]),f=Math.max(m,p(d[1]||"")),m=s?Math.max(m,s.getFullYear()):m,f=a?Math.min(f,a.getFullYear()):f,e.yearshtml+="<select class='ui-datepicker-year' data-handler='selectYear' data-event='change'>";f>=m;m++)e.yearshtml+="<option value='"+m+"'"+(m===i?" selected='selected'":"")+">"+m+"</option>";e.yearshtml+="</select>",b+=e.yearshtml,e.yearshtml=null}return b+=this._get(e,"yearSuffix"),y&&(b+=(!n&&g&&v?"":"&#xa0;")+_),b+="</div>"},_adjustInstDate:function(e,t,i){var s=e.drawYear+("Y"===i?t:0),a=e.drawMonth+("M"===i?t:0),n=Math.min(e.selectedDay,this._getDaysInMonth(s,a))+("D"===i?t:0),r=this._restrictMinMax(e,this._daylightSavingAdjust(new Date(s,a,n)));e.selectedDay=r.getDate(),e.drawMonth=e.selectedMonth=r.getMonth(),e.drawYear=e.selectedYear=r.getFullYear(),("M"===i||"Y"===i)&&this._notifyChange(e)},_restrictMinMax:function(e,t){var i=this._getMinMaxDate(e,"min"),s=this._getMinMaxDate(e,"max"),a=i&&i>t?i:t;return s&&a>s?s:a},_notifyChange:function(e){var t=this._get(e,"onChangeMonthYear");t&&t.apply(e.input?e.input[0]:null,[e.selectedYear,e.selectedMonth+1,e])},_getNumberOfMonths:function(e){var t=this._get(e,"numberOfMonths");return null==t?[1,1]:"number"==typeof t?[1,t]:t},_getMinMaxDate:function(e,t){return this._determineDate(e,this._get(e,t+"Date"),null)},_getDaysInMonth:function(e,t){return 32-this._daylightSavingAdjust(new Date(e,t,32)).getDate()},_getFirstDayOfMonth:function(e,t){return new Date(e,t,1).getDay()},_canAdjustMonth:function(e,t,i,s){var a=this._getNumberOfMonths(e),n=this._daylightSavingAdjust(new Date(i,s+(0>t?t:a[0]*a[1]),1));return 0>t&&n.setDate(this._getDaysInMonth(n.getFullYear(),n.getMonth())),this._isInRange(e,n)},_isInRange:function(e,t){var i,s,a=this._getMinMaxDate(e,"min"),n=this._getMinMaxDate(e,"max"),r=null,o=null,h=this._get(e,"yearRange");return h&&(i=h.split(":"),s=(new Date).getFullYear(),r=parseInt(i[0],10),o=parseInt(i[1],10),i[0].match(/[+\-].*/)&&(r+=s),i[1].match(/[+\-].*/)&&(o+=s)),(!a||t.getTime()>=a.getTime())&&(!n||t.getTime()<=n.getTime())&&(!r||t.getFullYear()>=r)&&(!o||o>=t.getFullYear())},_getFormatConfig:function(e){var t=this._get(e,"shortYearCutoff");return t="string"!=typeof t?t:(new Date).getFullYear()%100+parseInt(t,10),{shortYearCutoff:t,dayNamesShort:this._get(e,"dayNamesShort"),dayNames:this._get(e,"dayNames"),monthNamesShort:this._get(e,"monthNamesShort"),monthNames:this._get(e,"monthNames")}},_formatDate:function(e,t,i,s){t||(e.currentDay=e.selectedDay,e.currentMonth=e.selectedMonth,e.currentYear=e.selectedYear);var a=t?"object"==typeof t?t:this._daylightSavingAdjust(new Date(s,i,t)):this._daylightSavingAdjust(new Date(e.currentYear,e.currentMonth,e.currentDay));return this.formatDate(this._get(e,"dateFormat"),a,this._getFormatConfig(e))}}),e.fn.datepicker=function(t){if(!this.length)return this;e.datepicker.initialized||(e(document).mousedown(e.datepicker._checkExternalClick),e.datepicker.initialized=!0),0===e("#"+e.datepicker._mainDivId).length&&e("body").append(e.datepicker.dpDiv);var i=Array.prototype.slice.call(arguments,1);return"string"!=typeof t||"isDisabled"!==t&&"getDate"!==t&&"widget"!==t?"option"===t&&2===arguments.length&&"string"==typeof arguments[1]?e.datepicker["_"+t+"Datepicker"].apply(e.datepicker,[this[0]].concat(i)):this.each(function(){"string"==typeof t?e.datepicker["_"+t+"Datepicker"].apply(e.datepicker,[this].concat(i)):e.datepicker._attachDatepicker(this,t)}):e.datepicker["_"+t+"Datepicker"].apply(e.datepicker,[this[0]].concat(i))},e.datepicker=new a,e.datepicker.initialized=!1,e.datepicker.uuid=(new Date).getTime(),e.datepicker.version="1.11.4",e.datepicker});	
	</script>
	<link href='//fonts.googleapis.com/css?family=Ubuntu+Mono:400,400italic,700,700italic|Droid+Sans:400,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
<!--	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>-->
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.9.1/styles/default.min.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.7/styles/color-brewer.min.css">

	<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.9.1/highlight.min.js"></script>
	<script>hljs.initHighlightingOnLoad();</script>	

	<style>
		body { font-family: "Droid Sans", sans-serif; font-size: 1.6em; padding-bottom: 8em;}
		a:focus {outline: none !important; text-decoration: none;}
		a:hover {text-decoration: underline;}
			.container { 
				padding: 0em 5%; 
				min-width: 54em;
				}
				.header_container {
					/*border-bottom: 1px solid #DDD;*/
					margin-bottom: 2em;
					margin-top: 5%;
					}
				.header_container > h1 {
					margin-bottom: 0.6em;
					}
					.version {
						color: #AAA;
						}
			/* alert + common warnings - START */
				.s2p-alert,
				.s2p-info-grey,
				.s2p-error,
				.s2p-success,
				.s2p-warn {
					position: relative;
					background-color: #f0f0f0;
					color: #666;
					padding: 1rem 1rem 1rem 6.2rem;
					border: 1px solid #ff813d;
					}
					.s2p-alert:after,
					.s2p-info-grey:after,
					.s2p-error:after,
					.s2p-success:after,
					.s2p-warn:after {
						position: absolute; left: 0px; top: 0px;
						font-family: "Glyphicons Halflings"; 
						font-style: normal;
						font-weight: 400;
						line-height: 1;						
						font-size: 2em;
						color: #FFF;
						padding: 0.18em 0.22em;
						background-color: #ff813d;
						height: 100%;
						}
						
					.s2p-alert:after { content: "\e101"; }
					
			/* error */
				.s2p-error { background-color: #F2DEDE; border-color: #A94442; color: #A94442;}
					.s2p-error:after { background-color: #A94442; content:"\e014"; }
						
			/* success */
				.s2p-success { background-color: #DFF0D8; border-color: #3C763D; color: #3C763D;}
					.s2p-success:after { background-color: #3C763D; content:"\e013"; }			
					
			/* warn */
				.s2p-warn { background-color: #D9EDF7; border-color: #31708F; color: #31708F; }
					.s2p-warn:after { background-color: #31708F; content: "\f456"; }					
						
			
			/* grey info */
				.s2p-info-grey { background-color: #f0f0f0; border-color: #DDD; }
					.s2p-info-grey:after { background-color: #DDD; content: "\e086"; }
					
					.post_url {
						font-size: 1.2em;
						font-weight: bold;
						}
	
		.s2p_form {
			margin-top: 1em;
			padding-top: 1em;
			border-top: 1px solid #DDD;
			}
			#api_form {
				position: relative;
				}
				#api_form > fieldset {
					margin-top: 1em;
					}
				#api_form > .form_field {}
					label {}
					.form_input {}
						.form_input input,
						.form_input select {
							min-width: 22em;
							}
							
				fieldset#method_get_parameters {
					margin-top: 1em;
					}
				fieldset {
					border: 2px solid #DDD;
					padding: 2em 1em 1em 1em;
					margin-bottom: 1em;
					position: relative;
					}
				fieldset.hover {
					border-color: #337AB7;
					}
					fieldset .form_field {
						}
						fieldset > label {
							position: absolute;
							left: 0px; top: 0px;
							}
							fieldset > label > a {
								padding: 0.1em 0.3em;
								background-color: #DDD;
								position: relative;
								top: -0.1em;
								}
							fieldset > label > a:hover {
								background-color: #337AB7;
								color: #FFF;
								text-decoration: none;
								}
								
				.form_field.submit_container {
					position: absolute;
					top: 0em;
					right: 0em;
					width: auto;
					}
					
					
			#api_result {
				}
		.datepicker, .datepickerdateonly { width: 120px !important; }
		
				#api_result > div {	
					}
					.http_headers_code {
						padding: 0.5em;
						margin: 0em;
						font-family: 'Ubuntu Mono', "Courier New", Courier, monospace; 
						width: 100%; 
						}
						.http_headers_code_title {
							width: 100%; 
							font-family: inherit !important; 
							font-weight: bold; 
							font-size: 1.3em;
							line-height: 2em;
							position: relative;
							padding-left: 2.3em;
							color: #FFF;
							margin-bottom: 0.2em;
							border-radius: 0.2em;
							}
						.http_headers_code_title a {
							color: #fff;
							width: 100%;
							display: block;
							}
							.http_headers_code_title:before {
								position: absolute; left: 0.2em; top: 0em;
								font-family: "Glyphicons Halflings"; 
								font-size: 1.1em;
								padding: 0em 0.22em;
								color: #FFF;
								border-radius: 0.2em;								
								}
							.http_headers_code_title:after {
								position: absolute; right: 1em; top: 0em;
								font-family: "Glyphicons Halflings"; 
								font-size: 0.6em;
								color: #FFF;
								}								
							.http_headers_code_title:after { content:"\e114"; }
							.http_headers_code_title.expanded:after { content: "\e113"; }
						.http_headers_code > pre {}	
						#s2p_api_request_body,
						#s2p_api_response_body {}
										
				.request {
					margin-bottom: 1em;
					width: 49%;
					float: left;
					}
					.http_headers_code.request_headers {}
						.request .http_headers_code_title {
							background-color: #5CB85C;
							}
						.http_headers_code_title:before {}
						.request_headers > pre {}
					.http_headers_code.request_body {}
						.request .http_headers_code_title {
							background-color: #5CB85C;
							}
						.request .http_headers_code_title:before {
							content:"\e169";
							}					
						#s2p_api_request_body_raw_toggler {
							margin-bottom: 0.3em;
							}
							#s2p_api_request_body_raw_toggler > a{
								padding-bottom: 0.1em;
								}
							#s2p_api_request_body_raw_toggler > a:hover {
								text-decoration: none;
								border-bottom: 0.1em solid #337AB7;
								border-bottom: 0.2em solid #DDD;
								}
							#s2p_api_request_body_raw_toggler > a:focus {
								outline: none !important;
								text-decoration: none !important;
								}
							#s2p_api_request_body_raw_toggler > a span.active{
								font-weight: bold;
								}
						#s2p_api_request_body_raw {}
						#s2p_api_request_body_formatted {}
					
				.response {
					width: 49%;
					float: right;
					}
					.http_headers_code.response_headers {}
						.response .http_headers_code_title {
							background-color: #5BC0DE;
							}
						.response .http_headers_code_title:before {}
						.response_headers > pre {}
					
				.http_headers_code.response_body {}
					.response .http_headers_code_title {}					
					.response .http_headers_code_title:before {
						content:"\e170";
						}					
					.response_body > pre {}				
				.http_headers_code.processed_response {}
					.http_headers_code_title {}
					.processed_response > pre {}				
				
				
					
			
			#s2p_api_result_toggler {
				margin: 2em 0em 1em 0em;
				padding-left: 3em;
				font-weight: bold;
				border-color: #BBB;
				}
				#s2p_api_result_toggler > li.active > a, 
				#s2p_api_result_toggler > li.active > a:focus, 
				#s2p_api_result_toggler > li.active > a:hover {
					border-color: #BBB #BBB transparent;
					}
				#s2p_api_result_toggler > li > a:hover {
					border-color: #EEE #EEE #BBB;
					}	
				#s2p_api_result_toggler > li > a:focus {
					outline: none !important;
					}
	

			pre {
				border-radius: 0.55em;
				}
				code.hljs {
					padding: 0.2em 0.6em;
					background-color: transparent;
					}


	
	
	
	
		.clearfix { clear: both; }
		/*.s2p_form { margin: 10px auto; width: 800px; }*/
		/*#s2p_api_result_toggler { width: 100%; text-align: right; margin-bottom: 10px; clear: both; }*/
		/*#s2p_api_form_toggler { width: 100%; text-align: left; margin-bottom: 10px; clear: both; }*/
		/*.s2p_form fieldset { margin-bottom: 5px; }*/
		.form_field { width: 100%; padding: 3px; min-height: 30px; margin-bottom: 5px; clear: both; }
		.form_field label { width: 16em; float: left; line-height: 25px; }
		.form_field label.mandatory:after { color: red; content: '*'; }
		.form_field .form_input { float: left; min-height: 30px; vertical-align: middle; }
		.form_field .form_input .form_input_regexp { margin: 0 5px; font-size: small; }
		.form_input_regexp_exp { display: none; border: 1px solid gray; margin: 0 5px; border-radius: 5px; background-color: white; padding: 5px; position: absolute; }
		.form_input_regexp_exp { cursor: pointer; }
		.form_field .form_input input { padding: 3px; }
		.form_field .form_input select { padding: 2px; max-width: 300px; }
		.form_field .form_input input:not([type='checkbox']) { padding: 3px; border: 1px solid #a1a1a1; }
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
	</style>



	<script type="text/javascript">
		$(document).ready(function() {
			$('fieldset > label > a').hover(
				function() {
					$( this ).parent().parent().addClass('hover');
				}, function() {
					$( this ).parent().parent().removeClass('hover');
				}
			);
		});
		
		function toggleResponseFormat(elem) {
			elem.find('span').toggleClass('active');
		}
		
		function toggle_container( id, elem )
		{
			var obj = $('#'+id);
			if( obj )
			{
				obj.slideToggle(200);
			}
			if (elem && elem.parent().hasClass('http_headers_code_title'))
				elem.parent().toggleClass('expanded');
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
			$('.datepickerdateonly').datepicker({
				firstDay: 1,
				dateFormat: 'yymmdd'
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
	<div class="container">
		<div class="header_container">
			<h1><?php echo self::s2p_t( 'Welcome to Smart2Pay SDK demo page!' )?></h1>
			<div class="version">
				<small class="clearfix"><?php echo self::s2p_t( 'SDK version' )?>: <?php echo S2P_SDK_VERSION?></small>
			</div>
			<p class="s2p-info-grey"><?php echo self::s2p_t( 'Please note that this page contains technical information which is intended to help developers start using our SDK.' )?></p>
		</div>	
	
	
<?php
    }

    public function display_footer( $params = false )
    {
        ?></body>
</html><?php
    }

}
