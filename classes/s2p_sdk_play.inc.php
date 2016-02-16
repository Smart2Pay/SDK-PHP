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
        if( !($this->_method_details = S2P_SDK_Method::get_all_methods()) )
        {
            if( self::st_has_error() )
                $this->copy_static_error();
            else
                $this->set_error( self::ERR_METHODS, 'Couldn\'t obtain methods information.' );

            return false;
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
                    <h5><?php echo self::s2p_t( 'Found %s functionalities', count( $method_functionalities ) );?>:</h5>
                    <ul>
                    <?php
                        foreach( $method_functionalities as $functionality_name => $functionality_arr )
                        {
                            ?>
                            <li><a href="javascript:void(0);" onclick="toggle_container( 'details_<?php echo $method_name.'_'.$functionality_name?>' )" class="functionality_name"><?php echo $functionality_name.' - '.$functionality_arr['name'];?></a>
                            <div id="details_<?php echo $method_name.'_'.$functionality_name?>" class="functionality_container clearfix" style="display: none;">
                            <p><?php echo self::s2p_t( 'Quick example on how to use method <em>%s</em> with functionality <em>%s</em>.', $method_name, $functionality_name );?></p>
                            <pre><code class="php sdk_sample_code"><?php echo $this->display_method_function_example( $method_arr['instance'], $functionality_name ); ?></code></pre>
                            </div>
							</li>
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

// Tells SDK we work on debugging mode or not
S2P_SDK\S2P_SDK_Module::st_debugging_mode( true );
// Tells SDK errors should be thrown instead of retrieving them with $obj->get_error() or S2P_SDK\S2P_SDK_Module::st_get_error()
S2P_SDK\S2P_SDK_Module::st_throw_errors( false );

$api_parameters = array();
// Uncomment line below if you want to override Site ID set in config.inc.php
// $api_parameters['site_id'] = '{PROVIDED_SITE_ID}';
// Uncomment line below if you want to override API Key set in config.inc.php
// $api_parameters['api_key'] = '{PROVIDED_APIKEY}';
// Uncomment line below if you want to override environment set in config.inc.php
// $api_parameters['environment'] = 'test'; // test or live
$api_parameters['method'] = '<?php echo $method_obj->get_method()?>';
$api_parameters['func'] = '<?php echo $func?>';

$api_parameters['get_variables'] = array(<?php

        if( !empty( $func_details['get_variables'] ) and is_array( $func_details['get_variables'] ) )
        {
            echo "\n";
            foreach( $func_details['get_variables'] as $var_arr )
            {
                $var_str = "\t".'\''.$var_arr['name'].'\' => ';

                if( !($type_arr = S2P_SDK_Scope_Variable::valid_type( $var_arr['type'] )) )
                    $type_arr = array( 'title' => '(Unknown_var_type)' );

                if( $var_arr['type'] == S2P_SDK_Scope_Variable::TYPE_STRING
                 or is_string( $var_arr['default'] ) )
                    $var_str .= '\''.$var_arr['default'].'\'';
                else
                    $var_str .= $var_arr['default'];

                $var_str .= ',';

                if( !empty( $var_arr['mandatory'] ) or !empty( $var_arr['display_name'] ) )
                {
                    $var_str .= ' //';
                    if( !empty( $var_arr['display_name'] ) )
                        $var_str .= ' '.$var_arr['display_name'];
                    if( !empty( $var_arr['mandatory'] ) )
                        $var_str .= ' '.strtoupper( self::s2p_t( 'mandatory' ) );
                }

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

                echo "\n".$this->display_method_params( $method_input_arr, $mandatory_arr, $hide_keys_arr, array( 'structure_obj' => $structure_obj ) );
            }
        }
?>);

$call_params = array();

$finalize_params = array();
$finalize_params['redirect_now'] = false;

if( !($call_result = S2P_SDK\S2P_SDK_Module::quick_call( $api_parameters, $call_params, $finalize_params )) )
{
    echo 'API call error: ';

    if( ($error_arr = S2P_SDK\S2P_SDK_Module::st_get_error())
    and !empty( $error_arr['display_error'] ) )
        echo $error_arr['display_error'];
    else
        echo 'Unknown error.';
} else
{
    echo 'API call time: '.$call_result['call_microseconds'].'ms<br/>'."\n";

    if( !empty( $call_result['finalize_result']['should_redirect'] )
    and !empty( $call_result['finalize_result']['redirect_to'] ) )
        echo '<br/>'."\n".
             'Go to <a href="'.$call_result['finalize_result']['redirect_to'].'">'.$call_result['finalize_result']['redirect_to'].'</a> to complete transaction<br/>'."\n".
             '<br/>'."\n";

    echo 'Call result:<br>'."\n".
    '<pre>';

    var_dump( $call_result['call_result'] );

    echo '</pre>';
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
        if( empty( $params['path'] ) )
            $params['path'] = '';
        if( empty( $params['structure_obj'] )
         or !is_object( $params['structure_obj'] )
         or !($params['structure_obj'] instanceof S2P_SDK_Scope_Structure) )
            $params['structure_obj'] = false;

        /** @var S2P_SDK_Scope_Structure $structure_obj */
        $structure_obj = false;
        if( !empty( $params['structure_obj'] ) )
            $structure_obj = $params['structure_obj'];

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

            $current_path = $params['path'].(!empty( $params['path'])?'.':'').$param_name;

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

                if( empty( $structure_obj )
                 or !($path_name = $structure_obj->path_to_display_name( $current_path, array( 'check_external_names' => false ) )) )
                    $path_name = '';

                $mandatory_str = '';
                if( !empty( $param_mandatory ) )
                    $mandatory_str = strtoupper( self::s2p_t( 'mandatory' ) );

                if( !empty( $mandatory_str ) or !empty( $path_name ) )
                    $var_str .= '// '.$mandatory_str.((!empty( $mandatory_str ) and !empty( $path_name ))?' - ':'').$path_name;

            } else
            {
                $var_str .= 'array( ';

                if( empty( $structure_obj )
                 or !($path_name = $structure_obj->path_to_display_name( $current_path, array( 'check_external_names' => false ) )) )
                    $path_name = '';

                $mandatory_str = '';
                if( !empty( $mandatory_arr[$param_name] ) )
                    $mandatory_str = strtoupper( self::s2p_t( 'mandatory' ) );

                if( !empty( $mandatory_str ) or !empty( $path_name ) )
                    $var_str .= '// '.$mandatory_str.((!empty( $mandatory_str ) and !empty( $path_name ))?' - ':'').$path_name;

                $var_str .= "\n";

                $new_params = $params;
                $new_params['indent_chars'] .= "\t";
                $new_params['level']++;
                $new_params['path'] = $current_path;

                if( !($array_str = $this->display_method_params( $param_value,
                                            (array_key_exists( $param_name, $mandatory_arr )?$mandatory_arr[$param_name]:array()),
                                            (array_key_exists( $param_name, $hide_keys_arr )?$hide_keys_arr[$param_name]:array()),
                                            $new_params ))
                  )
                    continue;

                $var_str .= $array_str.$params['indent_chars'].'),';
            }

            $full_value_str .= $var_str."\n";

        }

        return $full_value_str;
    }

    private function display_header()
    {
        ?><html>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php self::s2p_t( 'SDK demo page' )?></title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<link href='http://fonts.googleapis.com/css?family=Ubuntu+Mono:400,400italic,700,700italic|Droid+Sans:400,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.9.1/styles/default.min.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.7/styles/color-brewer.min.css">

	<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.9.1/highlight.min.js"></script>
	<script>hljs.initHighlightingOnLoad();</script>	
	<style>
		a.method_name,
		a.functionality_name { font-weight: bold; }	
		a {
			padding: 0.1em 0.3em;
			position: relative; left: -0.3em;
			}
		a:hover {
			background-color: #337ab7;
			color: #FFF;
			text-decoration: none;
			}
		a:focus {
			text-decoration: none;
			outline: none !important;
			}
		.clearfix { clear: both; }
		body { font-family: "Droid Sans", sans-serif; font-size: 1.6em; }
			.container { padding: 0em 5%; }
				.header_container {
					/*border-bottom: 1px solid #DDD;*/
					margin-bottom: 2em;
					margin-top: 5%;
					}
				.header_container > h1 {
					margin-bottom: 0.6em;
					}
					.header_container > p {
						position: relative;
						padding: 0.5em 0.5em 0.5em 3em;
						border-left: 1px solid #337ab7;
						border: 1px solid #DDD;
						background-color: #f0f0f0;
						}
					.header_container > p:before {
						position: absolute;
						top: 0em;
						left: 0em;
						display: inline-block;
						font-family: "Glyphicons Halflings";
						font-style: normal;
						font-weight: 400;
						line-height: 1;
						transition: all 100ms ease-in-out;
						background-color: #337ab7;
						background-color: #DDD;
						color: #FFF;
						font-size: 1.75em;					
						content:"\e086";
						padding: 0.19em;
						height: 100%;
						}
					.version {
						color: #AAA;
						}
				.container > ul {}
					.container > ul > li {
						margin-bottom: 1em;
						margin-top: 1em;
						}
						.container > ul > li.expanded > small {
							/*border-bottom: 1px solid #CCC;*/
							padding-bottom: 0.5em;
							}

			.method_container { 
				margin: 0.4em 0 1em 0;
				padding: 1em;
				transition: all 200ms ease-in-out;
				overflow: hidden;
				border-bottom: 1px solid #CCC;
				border-left: 1px solid #CCC;
				}
				.method_container h5 {
					font-style: italic;
					color: #A0A0A0;
					margin-top: 0em;
					}
				.method_container > ul {
					}
					.method_container > ul > li {
						margin: 0.3em 0em 0.3em 0em;
						}
			.functionality_container { padding: 1em 0; }
			
				/* list arrows - START */
				.container > ul,
				.method_container > ul {
					list-style: none;
					}
					.container > ul > li,
					.method_container > ul > li { position: relative;	}
					.container > ul > li > a:before,
					.method_container > ul > li > a:before {	
						position: absolute;
						top: 0.5em;
						left: -1.5em;
						display: inline-block;
						font-family: "Glyphicons Halflings";
						font-style: normal;
						font-weight: 400;
						line-height: 1;
						transition: all 100ms ease-in-out;
						color: #AAA;
						font-size: 0.7em;
						}
					.method_container > ul > li > a:before {
						color: #DDD;
						}
					.container > ul > li > a:hover:before,
					.method_container > ul > li > a:hover:before {
						color: #333;
						left: -2em;
						}
					.container > ul > li.expanded > a:hover:before,
					.method_container > ul > li.expanded > a:hover:before  {
						left: -1.5em;
						}
					.container > ul > li > a:before,
					.method_container > ul > li > a:before { content:"\e080"; }
					.container > ul > li.expanded > a:before,
					.method_container > ul > li.expanded > a:before { content:"\e114"; }
					/* list arrows - STOP */			
			
			pre {
				border-radius: 0.55em;
				}
				code.hljs.sdk_sample_code {
					padding: 0.2em 0.6em;
					background-color: transparent;
					}
	</style>
	<script type="text/javascript">
		function toggle_container( id )
		{
			var obj = $( '#'+id );
			obj.slideToggle(150);
			obj.parent().toggleClass('expanded');
		}
	</script>
</head>
<body>

        
      
	<div class="container">
		<div class="header_container">
			<h1><?php echo self::s2p_t( 'Welcome to Smart2Pay SDK demo page!' )?></h1>
			<div class="version"><small>SDK version <?php echo S2P_SDK_VERSION?></small></div>
			<p><?php echo self::s2p_t( 'Please note that this page contains technical information which is intended to help developers start using our SDK.' )?></p>
		</div>
		<?php
			}

			private function display_footer()
			{
				?>
	</div>
</body>
</html><?php
    }

}
