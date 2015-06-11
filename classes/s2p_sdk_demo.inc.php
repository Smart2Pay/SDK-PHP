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

    public function init_method( $method, $func )
    {
        $this->_post_data = array();

        $method = trim( $method );
        $func = trim( $func );
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

        if( !($func_details = $instance->valid_functionality( $func )) )
        {
            $this->set_error( self::ERR_INSTANTIATE_METHOD, self::s2p_t( 'Error instantiating method %s.', ucfirst( $method ) ) );
            return false;
        }

        $this->_method = $instance;
        $this->_method_func = $func;
        $this->_method_func_details = $func_details;

        return true;
    }

    public function display_form_common_fields()
    {
        ?>
        <?php
    }

    public function display_init_payment_form( $params = false )
    {
        if( !($base_url = $this->base_url()) or true )
        {
            $this->set_error( self::ERR_BASE_URL, self::s2p_t( 'Couldn\'t guess base URL. Please set it manually using '.__CLASS__.'::base_url( url ) method.' ) );
            return false;
        }

        if( !$this->init_method( 'payments', 'payment_init' ) )
            return false;

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['post_params'] ) )
            $params['post_params'] = self::extract_post_data();

        $post_params = $params['post_params'];

        ?>
        <p>Detected script location: <em><?php echo $base_url?>samples/init_payment.php</em><br/>
        If this URL doesn't look right you will have to edit the script and set right base URL using $demo->base_url(); call.</p>
        <form action="<?php echo $base_url?>samples/init_payment.php" method="post">
        <?php $this->display_form_common_fields( $post_params ); ?>
        </form>
        <?php

        return true;
    }

    public function display_header()
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