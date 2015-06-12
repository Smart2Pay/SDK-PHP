<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_PATH' ) )
    die( 'Something went bad' );

include_once( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' );

class S2P_SDK_Demo extends S2P_SDK_Module
{
    const ERR_BASE_URL = 1, ERR_INSTANTIATE_METHOD = 2, ERR_FUNCTIONALITY = 3;

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

    // We assume all calls to Demo class are made from samples directory inside SDK root directory...
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
            'mparams' => array(),
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
         or !($instance = self::get_instance( 'S2P_SDK_Meth_'.ucfirst( $method ) )) )
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

        ob_start();
        ?>
        <input type="hidden" name="foobar" value="1" />
        <div class="form_field">
            <label for="api_key">API Key</label>
            <div class="form_input"><input type="text" id="api_key" name="api_key" value="<?php self::form_str( $post_arr['api_key'] )?>" style="width: 350px;" /></div>
        </div>

        <div class="form_field">
            <label for="environment">Environment</label>
            <div class="form_input"><select id="environment" name="environment">
                <option value="test" <?php echo ($post_arr['environment']=='test'?'selected="selected"':'')?>>Test</option>
                <option value="live" <?php echo ($post_arr['environment']=='live'?'selected="selected"':'')?>>Live</option>
            </select></div>
        </div>

        <div class="form_field">
            <label for="method">Method</label>
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
            <label for="func">Functionality</label>

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

    private function get_form_method_get_params_fields( $post_arr, $form_arr )
    {
        if( empty( $this->_method )
         or empty( $form_arr ) or !is_array( $form_arr ) or empty( $form_arr['form_name'] )
         or empty( $post_arr['func'] )
         or !($func_details = $this->_method->valid_functionality( $post_arr['func'] ))
         or empty( $func_details['get_variables'] ) or !is_array( $func_details['get_variables'] ) )
            return '';

        $post_arr = self::validate_post_data( $post_arr );

        ob_start();
        ?>
        <fieldset id="method_get_parameters">
        <label for="method_get_parameters"><a href="javascript:void(0);" onclick="toggle_container( 'method_get_parameters_container' )"><strong>Get parameters</strong></a></label>
        <div id="method_get_parameters_container" style="display: block;">

        <?php
        foreach( $func_details['get_variables'] as $get_var )
        {
            $field_name = 'gvars['.$get_var['name'].']';
            $field_id = 'gvar_'.$get_var['name'];

            if( isset( $post_arr['gvars'][$get_var['name']] ) )
                $field_value = $post_arr['gvars'][$get_var['name']];
            elseif( isset( $get_var['default'] ) )
                $field_value = $get_var['default'];
            else
                $field_value = '';

            if( !($field_type_arr = S2P_SDK_Scope_Variable::valid_type( $get_var['type'] )) )
                $field_type_arr = array( 'title' => '[undefined]' );

            ?>
            <div class="form_field">
                <label for="<?php echo $field_id?>"><?php echo $get_var['name'].(!empty( $get_var['mandatory'] )?'<span style="color:red;font-weight: bold;">*</span>':'')?></label>
                <div class="form_input"><?php
                if( !empty( $get_var['value_source'] ) )
                {
                    if( S2P_SDK_Values_Source::valid_type( $get_var['value_source'] )
                    and ($value_source_obj = new S2P_SDK_Values_Source( $get_var['value_source'] ))
                    and ($options_value = $value_source_obj->get_option_values()) )
                    {
                        ?><select id="<?php echo $field_id?>" name="<?php echo $field_name?>">
                        <option value="0"> - <?php echo self::s2p_t( 'Choose an option' );?> - </option><?php
                        foreach( $options_value as $key => $val )
                        {
                            ?><option value="<?php echo self::form_str( $key )?>"><?php echo $val;?></option><?php
                        }
                        ?></select><?php
                    }
                } else
                {
                    ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" /><?php

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
            or empty( $func_details['request_structure'] )
        )
            return '';

        $post_arr = self::validate_post_data( $post_arr );

        /** @var S2P_SDK_Scope_Structure $structure_obj */
        $structure_obj = $func_details['request_structure'];

        $extraction_arr                        = array();
        $extraction_arr['nullify_full_object'] = true;
        $extraction_arr['skip_regexps']        = true;

        if( ($method_definition = $structure_obj->get_validated_definition()) )
        {
            $mandatory_arr = array();
            if( empty( $func_details['mandatory_in_request'] )
             or !($mandatory_arr = $structure_obj->transfrom_keys_to_internal_names( $func_details['mandatory_in_request'] )) )
                $mandatory_arr = array();

            $hide_keys_arr = array();
            if( empty( $func_details['hide_in_request'] )
             or !($hide_keys_arr = $structure_obj->transfrom_keys_to_internal_names( $func_details['hide_in_request'] )) )
                $hide_keys_arr = array();

            ob_start();
            ?>
            <fieldset id="method_parameters">
            <label for="method_parameters"><a href="javascript:void(0);" onclick="toggle_container( 'method_parameters_container' )"><strong>Method parameters</strong></a></label>
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

    public static function extract_field_value( $post_arr, $dotted_path )
    {
        return 'working on it';
    }

    private function get_form_method_parameters_fields_detailed( $structure_definition, $mandatory_arr, $hide_keys_arr, $post_arr, $form_arr, $params = false )
    {
        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['path'] ) )
            $params['path'] = '';
        if( empty( $params['level'] ) )
            $params['level'] = -1;

        if( empty( $mandatory_arr ) or !is_array( $mandatory_arr ) )
            $mandatory_arr = array();
        if( empty( $hide_keys_arr ) or !is_array( $hide_keys_arr ) )
            $hide_keys_arr = array();

        if( empty( $structure_definition ) or !is_array( $structure_definition ) )
            return '';

        $params['path'] .= (!empty( $params['path'] )?'.':'').$structure_definition['name'];
        $params['level']++;

        if( empty( $structure_definition['structure'] ) or !is_array( $structure_definition['structure'] ) )
        {
            // display single element...
            if( array_key_exists( $structure_definition['name'], $hide_keys_arr ) )
                return '';

            $field_id = str_replace( '.', '_', $params['path'] );
            $field_name = 'mparams['.str_replace( '.', '][', $params['path'] ).']';
            $field_value = self::extract_field_value( $post_arr, $params['path'] );

            if( !($field_type_arr = S2P_SDK_Scope_Variable::valid_type( $structure_definition['type'] )) )
                $field_type_arr = array( 'title' => '[undefined]' );

            $field_mandatory = false;
            if( array_key_exists( $structure_definition['name'], $mandatory_arr ) )
                $field_mandatory = true;

            ob_start();
            ?>
            <div class="form_field">
                <label for="<?php echo $field_id?>" title="<?php echo self::form_str( $params['path'] )?>"><?php echo $structure_definition['name'].(!empty( $field_mandatory )?'<span style="color:red;font-weight: bold;">*</span>':'')?></label>
                <div class="form_input"><?php
                    if( !empty( $structure_definition['value_source'] ) )
                    {
                        if( S2P_SDK_Values_Source::valid_type( $structure_definition['value_source'] )
                        and ($value_source_obj = new S2P_SDK_Values_Source( $structure_definition['value_source'] ))
                        and ($options_value = $value_source_obj->get_option_values()) )
                        {
                            ?><select id="<?php echo $field_id?>" name="<?php echo $field_name?>">
                            <option value="0"> - <?php echo self::s2p_t( 'Choose an option' );?> - </option><?php
                            foreach( $options_value as $key => $val )
                            {
                                ?><option value="<?php echo self::form_str( $key )?>"><?php echo $val;?></option><?php
                            }
                            ?></select><?php
                        }
                    } else
                    {
                        ?><input type="text" id="<?php echo $field_id?>" name="<?php echo $field_name?>" value="<?php echo self::form_str( $field_value )?>" /><?php

                        echo ' ('.$field_type_arr['title'].')';
                    }

                    if( $structure_definition['type'] == S2P_SDK_Scope_Variable::TYPE_DATETIME )
                    {
                        echo ' - yyyymmddhhmmss';
                    }
                ?></div>
            </div>
            <?php

            $buf = ob_get_clean();

            return $buf;
        }

        $field_id = str_replace( '.', '_', $params['path'] );

        $structure_buffer = '<fieldset id="mparam_'.$field_id.'">'.
            '<label for="mparam_'.$field_id.'"><a href="javascript:void(0);" onclick="toggle_container( \'mparam_container_'.$field_id.'\' )"><strong>'.$params['path'].'</strong></a></label>'.
            '<div id="mparam_container_'.$field_id.'" style="display: block;">';


        foreach( $structure_definition['structure'] as $element_definition )
        {
            if( ($element_buffer = $this->get_form_method_parameters_fields_detailed( $element_definition,
                ( array_key_exists( $element_definition['name'], $mandatory_arr ) ? $mandatory_arr[ $element_definition['name'] ] : array() ),
                ( array_key_exists( $element_definition['name'], $hide_keys_arr ) ? $hide_keys_arr[ $element_definition['name'] ] : array() ),
                $post_arr,
                $form_arr,
                $params ) ) )
                $structure_buffer .= $element_buffer;
        }

        $structure_buffer .= '</div></fieldset>';


        return $structure_buffer;
    }

    public function get_init_payment_form( $params = false )
    {
        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        $params['method'] = 'payments';
        //$params['func'] = 'payment_init';
        $params['func'] = 'payments_list';

        $params['submit_text'] = self::s2p_t( 'Initiate payment' );

        $params['form_action_suffix'] = 'samples/init_payment.php';

        return $this->get_form( $params );
    }

    public function get_form( $params = false )
    {
        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['form_action_suffix'] ) )
            $params['form_action_suffix'] = '';
        if( empty( $params['post_params'] ) )
            $params['post_params'] = self::extract_post_data();

        if( empty( $params['base_url'] ) )
            $params['base_url'] = $this->base_url();

        if( empty( $params['submit_text'] ) )
            $params['submit_text'] = 'Submit';
        if( empty( $params['form_name'] ) )
            $params['form_name'] = 's2p_demo_form';

        if( empty( $params['base_url'] ) )
        {
            $this->set_error( self::ERR_BASE_URL, self::s2p_t( 'Couldn\'t guess base URL. Please set it manually using '.__CLASS__.'::base_url( url ) method.' ) );
            return false;
        }

        $post_params = self::validate_post_data( $params['post_params'] );

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

        //if( empty( $method ) or empty( $func ) )
        //{
        //    $this->set_error( self::ERR_BASE_URL, self::s2p_t( 'Please provide method and functionality.' ) );
        //    return false;
        //}

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
        <p>Form will be submitted to: <em><?php echo $params['base_url'].$params['form_action_suffix']?></em><br/>
        If this URL doesn't look right you will have to edit the script and set right base URL using $demo->base_url(); call.</p>
        <form name="<?php echo $params['form_name']?>" action="<?php echo $params['base_url'].$params['form_action_suffix']?>" method="post" class="s2p_form">

        <?php echo $this->get_form_common_fields( $post_params, $form_arr ); ?>

        <?php echo $this->get_form_method_get_params_fields( $post_params, $form_arr ); ?>

        <?php echo $this->get_form_method_parameters_fields( $post_params, $form_arr ); ?>

        <div class="form_field" style="text-align: center;">
            <input type="submit" id="do_submit" name="do_submit" value="<?php echo self::form_str( $params['submit_text'] );?>" />
        </div>

        </form>
        <?php
        $buf = ob_get_clean();

        return $buf;
    }

    public function display_header()
    {
        ?><html><head>
<title><?php self::s2p_t( 'SDK demo page' )?></title>
<style>
.clearfix { clear: both; }
.s2p_form { margin: 10px auto; width: 800px; }
.s2p_form fieldset { margin-bottom: 5px; }
.form_field { clear: both; width: 100%; padding: 3px; min-height: 30px; margin-bottom: 5px; }
.form_field label { width: 250px; float: left; line-height: 25px; }
.form_field .form_input { float: left; min-height: 30px; vertical-align: middle; }
.form_field .form_input input { padding: 3px; }
.form_field .form_input select { padding: 2px; max-width: 300px; }
.form_field .form_input input:not([type='checkbox']) { padding: 3px; border: 1px solid #a1a1a1; }
</style>
<script type="text/javascript">
function toggle_container( id )
{
    var obj = document.getElementById(id);
    if( obj )
    {
        if( obj.style.display == 'none' )
            obj.style.display = 'block';
        else
            obj.style.display = 'none';
    }
}
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
</head>
<body>
<h1>Welcome to Smart2Pay SDK demo page!</h1>
<p>Please note that this page contains technical information which is intended to help developers start using our SDK.</p>
<small class="clearfix">SDK version <?php echo S2P_SDK_VERSION?></small>
<?php
    }

    public function display_footer()
    {
        ?></body>
</html><?php
    }

}