<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_PATH' ) )
    die( 'Something went bad' );

include_once( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' );

class S2P_SDK_Play extends S2P_SDK_Module
{
    const ERR_METHODS = 1, ERR_INSTANTIATE_METHOD = 2;

    private $_method = null;
    private $_func = null;

    private $_method_details = array();

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed If this function returns false it will consider module is not initialized correctly and will not return class instance
     */
    public function init( $module_params = false )
    {
        if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_PATH' ) )
            die( 'SDK is not correctly configured. Please check bootstrap script.' );

        $methods_dir = S2P_SDK_DIR_METHODS;
        if( substr( $methods_dir, -1 ) == '/' )
            $methods_dir = substr( $methods_dir, 0, -1 );

        if( !@is_dir( $methods_dir )
         or !@file_exists( S2P_SDK_DIR_METHODS.'s2p_sdk_method.inc.php' ) )
            die( 'SDK is not correctly configured. Please check bootstrap script.' );

        return true;
    }

    private function get_method_details()
    {
        if( !($method_files_arr = @glob( S2P_SDK_DIR_METHODS.'s2p_sdk_meth_*.inc.php' ))
         or !is_array( $method_files_arr ) )
        {
            $this->set_error( self::ERR_METHODS, 'No methods found in this SDK.' );
            return false;
        }

        $this->_method_details = array();
        foreach( $method_files_arr as $method_file )
        {
            if( !preg_match( '@'.S2P_SDK_DIR_METHODS.'s2p_sdk_meth_([a-zA-Z0-9_-]+).inc.php@', $method_file, $matches )
             or !is_array( $matches ) or empty( $matches[1] ) )
                continue;

            if( !($instance = self::get_instance( 'S2P_SDK_Meth_'.ucfirst( $matches[1] ) )) )
            {
                if( self::st_has_error() )
                    $this->copy_static_error();

                else
                    $this->set_error( self::ERR_INSTANTIATE_METHOD, self::s2p_t( 'Error instantiating method %s.', ucfirst( $matches[1] ) ) );

                return false;
            }

            $this->_method_details[$matches[1]] = array(
                'file' => $method_file,
                'instance' => $instance,
            );
        }

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

    public function play()
    {
        if( !$this->get_method_details() )
            return false;

        $this->display_header();

        ?><h3><?php echo self::s2p_t( 'Found %s methods', count( $this->_method_details ) )?>:</h3><ul><?php
        foreach( $this->_method_details as $method_name => $method_arr )
        {
            if( empty( $method_arr['instance'] ) )
                continue;

            /** @var S2P_SDK_Method $instance */
            $instance = $method_arr['instance'];

            ?><li><a href="javascript:void(0);" onclick="toggle_container( 'details_<?php echo $method_name?>' )" class="method_name"><?php echo $method_name.' - '.$instance->get_name();?></a><br/><small><?php echo $instance->get_short_description();?></small><?php

            if( ($method_functionalities = $instance->get_functionalities()) )
            {
                ?>
                <div id="details_<?php echo $method_name?>" class="method_container clearfix" style="display: none;">
                    <h5><?php echo self::s2p_t( 'Found %s functionalities:', count( $method_functionalities ) );?></h5>
                    <ul>
                    <?php
                        foreach( $method_functionalities as $functionality_name => $functionality_arr )
                        {
                            ?>
                            <li><a href="javascript:void(0);" onclick="toggle_container( 'details_<?php echo $method_name.'_'.$functionality_name?>' )" class="functionality_name"><?php echo $functionality_name.' - '.$functionality_arr['name'];?></a></li>
                            <div id="details_<?php echo $method_name.'_'.$functionality_name?>" class="functionality_container clearfix" style="display: none;">
                            <p><?php echo self::s2p_t( 'Quick example on how to use method <em>%s</em> with functionality <em>%s</em>.', $method_name, $functionality_name );?></p>
                            <pre class="sdk_sample_code"><code><?php echo $this->display_method_function_example( $method_arr['instance'], $functionality_name ); ?></code></pre>
                            </div>
                            <?php
                        }
                    ?>
                    </ul>
                </div>
                <?php
            }

            ?></li><?php
        }
        ?></ul><?php

        $this->display_footer();

        return true;
    }

    /**
     * Returns buffer of sample PHP code for specific method and functionality
     * @param S2P_SDK_Method $method_obj
     * @param string $func
     *
     * @return string Example buffer
     */
    public function display_method_function_example( $method_obj, $func )
    {
        if( empty( $method_obj ) or !is_object( $method_obj )
         or !($method_obj instanceof S2P_SDK_Method)
         or !($func_details = $method_obj->valid_functionality( $func )) )
            return '';

        ob_start();
        echo '<'.'?php'."\n";
        ?>

include( 'bootstrap.php' );

$api_parameters = array();
$api_parameters['api_key'] = '{PROVIDED_APIKEY}';
$api_parameters['environment'] = 'test'; // test or live
$api_parameters['method'] = '<?php echo $method_obj->get_method()?>';
$api_parameters['func'] = '<?php echo $func?>';

$api_parameters['get_variables'] = array(<?php

        if( !empty( $func_details['get_variables'] ) and is_array( $func_details['get_variables'] ) )
        {
            echo "\n";
            foreach( $func_details['get_variables'] as $var_arr )
            {
                $var_str = "\t".'\''.$var_arr['name'].'\' => ';

                if( !($type_str = S2P_SDK_Scope_Variable::valid_type( $var_arr['type'] )) )
                    $type_str = '(Unknown_var_type)';

                if( $var_arr['type'] == S2P_SDK_Scope_Variable::TYPE_STRING
                 or is_string( $var_arr['default'] ) )
                    $var_str .= '\''.$var_arr['default'].'\'';
                else
                    $var_str .= $var_arr['default'];

                $var_str .= ',';

                if( !empty( $var_arr['mandatory'] ) )
                    $var_str .= ' // Mandatory';

                echo $var_str."\n";
            }
        }
?>);
$api_parameters['method_params'] = array(<?php

        if( !empty( $func_details['request_structure'] ) )
        {
            /** @var S2P_SDK_Scope_Structure $structure_obj */
            $structure_obj = $func_details['request_structure'];

            $extraction_arr = array();
            $extraction_arr['nullify_full_object'] = true;
            $extraction_arr['skip_regexps'] = true;

            if( ($method_input_arr = $structure_obj->extract_info_from_response_array( array( 'foobar' => 1 ), $extraction_arr )) )
            {
                $mandatory_arr = array();
                if( empty( $func_details['mandatory_in_request'] )
                 or !($mandatory_arr = $structure_obj->transfrom_keys_to_internal_names( $func_details['mandatory_in_request'] )) )
                    $mandatory_arr = array();

                $hide_keys_arr = array();
                if( empty( $func_details['hide_in_request'] )
                 or !($hide_keys_arr = $structure_obj->transfrom_keys_to_internal_names( $func_details['hide_in_request'] )) )
                    $hide_keys_arr = array();

                echo "\n".$this->display_method_params( $method_input_arr, $mandatory_arr, $hide_keys_arr );
            }
        }
?>);

try
{
    /** @var S2P_SDK\S2P_SDK_API $api */
    if( !($api = S2P_SDK\S2P_SDK_Module::get_instance( 'S2P_SDK_API', $api_parameters )) )
        var_dump( S2P_SDK\S2P_SDK_Module::st_get_error() );

    else
    {
        if( !$api->do_call() )
        {
            echo 'API call time: '.$api->get_call_time().'ms<br/>';
            var_dump( $api->get_error() );
        } else
        {
            echo 'API call time: '.$api->get_call_time().'ms<br/>';
            echo 'Successful API call:<br/><hr/><br/>';
            var_dump( $api->get_result() );
        }
    }
} catch( Exception $ex )
{
    var_dump( $ex );
}

<?php
        $buf = ob_get_clean();

        $buf = htmlspecialchars( $buf );

        return $buf;
    }

    private function display_method_params( $method_params, $mandatory_arr, $hide_keys_arr, $params = false )
    {
        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['indent_chars'] ) )
            $params['indent_chars'] = "\t";
        if( empty( $params['level'] ) )
            $params['level'] = 0;

        if( empty( $mandatory_arr ) or !is_array( $mandatory_arr ) )
            $mandatory_arr = array();
        if( empty( $hide_keys_arr ) or !is_array( $hide_keys_arr ) )
            $hide_keys_arr = array();

        if( empty( $method_params ) or !is_array( $method_params ) )
            return '';

        $full_value_str = '';
        foreach( $method_params as $param_name => $param_value )
        {
            if( !is_array( $param_value )
            and array_key_exists( $param_name, $hide_keys_arr ) )
                continue;

            $var_str = $params['indent_chars'].'\''.$param_name.'\' => ';

            if( !is_array( $param_value ) )
            {
                $param_mandatory = false;
                if( array_key_exists( $param_name, $mandatory_arr ) )
                {
                    $param_mandatory = true;
                    $param_value = $mandatory_arr[$param_name];
                }

                if( is_string( $param_value ) )
                    $var_str .= '\''.$param_value.'\'';
                elseif( is_bool( $param_value ) )
                    $var_str .= (!empty( $param_value )?'true':'false');
                elseif( is_null( $param_value ) )
                    $var_str .= 'null';
                else
                    $var_str .= $param_value;

                $var_str .= ', ';

                if( !empty( $param_mandatory ) )
                    $var_str .= ' // Mandatory';
            } else
            {
                $var_str .= 'array(';

                if( !empty( $mandatory_arr[$param_name] ) )
                    $var_str .= ' // Mandatory';

                $var_str .= "\n";

                $new_params = $params;
                $new_params['indent_chars'] .= "\t";
                $new_params['level']++;

                if( ($array_str = $this->display_method_params( $param_value,
                                            (array_key_exists( $param_name, $mandatory_arr )?$mandatory_arr[$param_name]:array()),
                                            (array_key_exists( $param_name, $hide_keys_arr )?$hide_keys_arr[$param_name]:array()),
                                            $new_params ))
                  )
                    $var_str .= $array_str;

                $var_str .= $params['indent_chars'].'),';
            }

            $full_value_str .= $var_str."\n";

        }

        return $full_value_str;
    }

    private function display_header()
    {
        ?><html><head>
<title><?php self::s2p_t( 'SDK demo page' )?></title>
<style>
.clearfix { clear: both; }
a.method_name { font-weight: bold; }
a.functionality_name { font-weight: bold; }
.method_container { margin: 0 0 20px 0; }
.functionality_container { margin: 10px 0; }
.sdk_sample_code { background-color: #cdcdcd; color: black; padding: 3px; }
</style>
<script type="text/javascript">
function toggle_container( id )
{
    var obj = document.getElementById( id );
    if( obj )
    {
        if( obj.style.display == 'none' )
            obj.style.display = 'block';
        else
            obj.style.display = 'none';
    }
}
</script>
</head>
<body>
<h1>Welcome to Smart2Pay SDK info page!</h1>
<p>Please note that this page contains technical information which is intended to help developers start using our SDK.</p>
<small class="clearfix">SDK version <?php echo S2P_SDK_VERSION?></small>
<?php
    }

    private function display_footer()
    {
        ?></body>
</html><?php
    }

}