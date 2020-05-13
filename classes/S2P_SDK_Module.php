<?php

namespace S2P_SDK;

abstract class S2P_SDK_Module extends S2P_SDK_Language
{
    const SDK_VERSION = '3.0.0';

    // These are methods that needs "special attention" in some cases
    const METH_SMARTCARDS_ID = 6, METH_BANK_TRANSFER = 1, METH_MULTIBANCO_SIBS = 20;

    const ERR_HOOK_REGISTRATION = 1000, ERR_STATIC_INSTANCE = 1001, ERR_API_QUICK_CALL = 1002, ERR_SDK_INIT = 1003;

    const EMAIL_REGEXP = '^[a-zA-Z0-9\._%+-]{1,100}@[a-zA-Z0-9\.-]{1,40}\.[a-zA-Z]{1,8}$';
    const IP_REGEXP = '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$';

    private static $instances = array();
    private static $hooks = array();
    private static $sdk_inited = false;
    private static $sdk_init_failed = false;

    private static $one_call_settings = array(
        'api_key' => '',
        'site_id' => 0,
        'environment' => '',
        'return_url' => '',
        'custom_base_url' => '',
    );

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed If this function returns false it will consider module is not initialized correctly and will not return class instance
     */
    abstract public function init( $module_params = false );

    /**
     * This method is called when destroy_instances() method is called inside any module.
     * This is ment to be destructor of instances.
     * Make sure you call destroy_instances() when you don't need any data held in any instances of modules
     *
     * @see destroy_instances()
     */
    abstract public function destroy();

    final public static function is_smartcards_method( $method_id )
    {
        return ((int)$method_id === self::METH_SMARTCARDS_ID );
    }

    final public static function sdk_inited( $mode = null )
    {
        if( $mode === null )
            return self::$sdk_inited;

        self::$sdk_inited = (!empty( $mode ));

        return self::$sdk_inited;
    }

    final public static function sdk_init( $root_dir = false )
    {
        if( self::sdk_inited() )
            return true;

        // Inhibit throwing errors (required for PSR-4 autoloading)
        self::st_prevent_throwing_errors( true );

        self::$sdk_init_failed = false;

        if( !defined( 'S2P_SDK_VERSION' ) )
        {
            define( 'S2P_SDK_VERSION', self::SDK_VERSION );

            if( $root_dir === false )
                $root_dir = @dirname( __DIR__ );

            $root_dir = rtrim( $root_dir, '/\\' );

            define( 'S2P_SDK_DIR_PATH', $root_dir.'/' );
            define( 'S2P_SDK_DIR_CLASSES', $root_dir.'/classes/' );
            define( 'S2P_SDK_DIR_STRUCTURES', $root_dir.'/structures/' );
            define( 'S2P_SDK_DIR_METHODS', $root_dir.'/methods/' );
            define( 'S2P_SDK_DIR_LANGUAGES', $root_dir.'/languages/' );
        }

        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Currencies.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Countries.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Rest_API_Request.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Rest_API_Codes.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Rest_API.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Helper.php' );

        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Values_Source_Methods.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Values_Source_Recurring_Methods.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Values_Sources_Article_Type.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Values_Sources_Article_Tax_Type.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Values_Sources_Preapproval_Frequency.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Values_Source.php' );

        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Scope_Variable.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Scope_Structure.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Generic_Error.php' );

        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Status.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Customer.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Address.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Article.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_3D_Secure_Data.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Device_Info.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_On_File.php' );

        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Details.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Customer_Details.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Reference_Details.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Preapproval_details.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Details.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Capture_Details.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Details.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Token_Details.php');
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchantsite_Details.php' );

        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Preapproval_Request.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Preapproval_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Preapproval_Response_List.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Request.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Request_List.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Response_List.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Types_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Types_Response_List.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Request.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Response_List.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Capture_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Capture_Response_List.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Payment_Request.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Refund_Request.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Refund_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Refund_Response_List.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payout_Request.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payout_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payout_Response_List.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Method_Validator.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Method_Option.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Method.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Method_List.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_User_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_User_Request.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchantsite.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchantsite_List.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchant_Request.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchant_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchant_Create_Request.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchant_Create_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Fraud_Details_Response.php' );

        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Authentication_Request.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Authentication_Response.php' );

        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Exchangerate_Response.php' );
        include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Dispute_Notification.php' );

        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_API.php' );

        include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Method.php' );
        include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Preapprovals.php' );
        include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Payments.php' );
        include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Cards.php' );
        include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Methods.php' );
        include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Users.php' );
        include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Merchantsites.php' );
        include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Exchangerates.php' );

        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Notification.php' );
        include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Return.php' );

        // R&D stuff...
        // include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Database.php' );
        // include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Database_Wrapper.php' );
        // include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Context.php' );

        self::set_multi_language( true );

        if(
            !self::define_language( self::LANG_EN, array(
                'title' => 'English',
                'files' => array( S2P_SDK_DIR_LANGUAGES.'en.csv' ),
            ) )
            or

            !self::define_language( self::LANG_RO, array(
                'title' => 'Romana',
                'files' => array( S2P_SDK_DIR_LANGUAGES.'ro.csv' ),
            ) )
        )
        {
            self::st_set_error( self::ERR_SDK_INIT, 'Couldn\'t initialize language system.' );
            return false;
        }

        if( @file_exists( S2P_SDK_DIR_PATH.'config.php' ) )
        {
            include_once(S2P_SDK_DIR_PATH.'config.php');
        } elseif( defined( 'S2P_SDK_CONFIG_PATH' )
              and (string)constant( 'S2P_SDK_CONFIG_PATH' ) !== ''
              and @file_exists( rtrim( S2P_SDK_CONFIG_PATH, '/' ).'/config.php' ) )
        {
            include_once( S2P_SDK_CONFIG_PATH.'config.php' );
        } elseif( @file_exists( S2P_SDK_DIR_PATH.'config.inc.php' ) )
        {
            include_once( S2P_SDK_DIR_PATH.'config.inc.php' );
        } elseif( !defined( 'S2P_SDK_SITE_ID' ) or !defined( 'S2P_SDK_API_KEY' ) or !defined( 'S2P_SDK_ENVIRONMENT' ) )
        {
            self::st_set_error( self::ERR_SDK_INIT, 'SDK config file not found. Please create Smart2Pay SDK configuration file.' );
            return false;
        }

        //
        // !!! If you want to customize bellow values use config.php file
        //
        // Set SDK in debugging mode (or not)
        self::st_debugging_mode( false );
        // display full trace with the error (or not)
        self::st_detailed_errors( false );
        // Favor throwing errors when setting errors in classes (or not)
        self::st_throw_errors( false );
        //
        // END !!!
        //

        self::sdk_inited( true );

        return true;
    }

    function __construct( $module_params = false )
    {
        if( !self::sdk_inited() )
            self::sdk_init();

        parent::__construct();

        if( $module_params !== null )
            $this->init( $module_params );
    }

    public static function one_call_settings( $settings = false )
    {
        if( $settings === false )
            return self::$one_call_settings;

        if( empty( $settings ) or !is_array( $settings ) )
            return false;

        foreach( self::$one_call_settings as $key => $val )
        {
            if( array_key_exists( $key, $settings ) )
                self::$one_call_settings[$key] = $settings[$key];
        }

        return self::$one_call_settings;
    }

    public static function reset_one_call_settings()
    {
        self::$one_call_settings = array(
            'api_key' => '',
            'site_id' => 0,
            'environment' => '',
            'return_url' => '',
            'custom_base_url' => '',
        );
    }

    private function module_init( $module_params = false )
    {
        return $this->init( $module_params );
    }

    public function destroy_all()
    {
        if( empty( self::$instances ) or !count( self::$instances ) )
            return;

        /** @var S2P_SDK_Module $module */
        foreach( self::$instances as $module )
        {
            $module->destroy();
        }
    }

    public static function get_api_configuration()
    {
        $return_arr = array();
        $return_arr['api_key'] = '';
        $return_arr['site_id'] = 0;
        $return_arr['environment'] = '';
        $return_arr['return_url'] = '';
        $return_arr['custom_base_url'] = '';

        if( !empty( self::$one_call_settings['site_id'] ) )
            $return_arr['site_id'] = self::$one_call_settings['site_id'];
        elseif( defined( 'S2P_SDK_FORCE_SITE_ID' ) and constant( 'S2P_SDK_FORCE_SITE_ID' ) )
            $return_arr['site_id'] = constant( 'S2P_SDK_FORCE_SITE_ID' );
        elseif( defined( 'S2P_SDK_SITE_ID' ) and constant( 'S2P_SDK_SITE_ID' ) )
            $return_arr['site_id'] = constant( 'S2P_SDK_SITE_ID' );

        if( !empty( self::$one_call_settings['api_key'] ) )
            $return_arr['api_key'] = self::$one_call_settings['api_key'];
        elseif( defined( 'S2P_SDK_FORCE_API_KEY' ) and constant( 'S2P_SDK_FORCE_API_KEY' ) )
            $return_arr['api_key'] = constant( 'S2P_SDK_FORCE_API_KEY' );
        elseif( defined( 'S2P_SDK_API_KEY' ) and constant( 'S2P_SDK_API_KEY' ) )
            $return_arr['api_key'] = constant( 'S2P_SDK_API_KEY' );

        if( !empty( self::$one_call_settings['environment'] ) )
            $return_arr['environment'] = self::$one_call_settings['environment'];
        elseif( defined( 'S2P_SDK_FORCE_ENVIRONMENT' ) and constant( 'S2P_SDK_FORCE_ENVIRONMENT' ) )
            $return_arr['environment'] = constant( 'S2P_SDK_FORCE_ENVIRONMENT' );
        elseif( defined( 'S2P_SDK_ENVIRONMENT' ) and constant( 'S2P_SDK_ENVIRONMENT' ) )
            $return_arr['environment'] = constant( 'S2P_SDK_ENVIRONMENT' );

        if( !empty( self::$one_call_settings['return_url'] ) )
            $return_arr['return_url'] = self::$one_call_settings['return_url'];
        elseif( defined( 'S2P_SDK_FORCE_PAYMENT_RETURN_URL' ) and constant( 'S2P_SDK_FORCE_PAYMENT_RETURN_URL' ) )
            $return_arr['return_url'] = constant( 'S2P_SDK_FORCE_PAYMENT_RETURN_URL' );
        elseif( defined( 'S2P_SDK_PAYMENT_RETURN_URL' ) and constant( 'S2P_SDK_PAYMENT_RETURN_URL' ) )
            $return_arr['return_url'] = constant( 'S2P_SDK_PAYMENT_RETURN_URL' );

        if( !empty( self::$one_call_settings['custom_base_url'] ) )
            $return_arr['custom_base_url'] = self::$one_call_settings['custom_base_url'];
        elseif( defined( 'S2P_SDK_FORCE_CUSTOM_BASE_URL' ) and constant( 'S2P_SDK_FORCE_CUSTOM_BASE_URL' ) )
            $return_arr['custom_base_url'] = constant( 'S2P_SDK_FORCE_CUSTOM_BASE_URL' );
        elseif( defined( 'S2P_SDK_CUSTOM_BASE_URL' ) and constant( 'S2P_SDK_CUSTOM_BASE_URL' ) )
            $return_arr['custom_base_url'] = constant( 'S2P_SDK_CUSTOM_BASE_URL' );

        return $return_arr;
    }

    /**
     * Entry point when making Smart2Pay API calls
     *
     * @param array $api_parameters Parameters passed to API object which contains request details
     * @param array|bool $call_params Additional parameters sent to S2P_SDK\S2P_SDK_API::do_call()
     * @param array|bool $finalize_params Array with parameters sent to
     * @param bool $singleton Should API object be instantiated as singleton
     *
     * @return array|bool Returns false on error (error available with S2P_SDK\S2P_SDK_Module::st_get_error())
     *
     * @see \S2P_SDK\S2P_SDK_Module::st_get_error()
     * @see \S2P_SDK\S2P_SDK_API::do_call()
     */
    final public static function quick_call( $api_parameters, $call_params = false, $finalize_params = false, $singleton = true )
    {
        self::st_reset_error();

        if( !self::sdk_inited()
        and !self::sdk_init() )
        {
            if( !self::st_has_error() )
                self::st_set_error( self::ERR_API_QUICK_CALL, self::s2p_t( 'Couldn\'t initialize SDK.' ) );

            return false;
        }

        if( empty( $api_parameters ) or !is_array( $api_parameters ) )
        {
            self::st_set_error( self::ERR_API_QUICK_CALL, self::s2p_t( 'Invalid API parameters.' ) );
            return false;
        }

        if( empty( $finalize_params ) or !is_array( $finalize_params ) )
            $finalize_params = array();

        if( !isset( $finalize_params['redirect_now'] ) )
            $finalize_params['redirect_now'] = true;
        else
            $finalize_params['redirect_now'] = (!empty( $finalize_params['redirect_now'] )?true:false);

        $return_arr = array();
        // Time of call (in microseconds)
        $return_arr['call_microseconds'] = 0;
        // Result of API call
        $return_arr['call_result'] = false;
        // API call details (request + response)
        $return_arr['call_details'] = false;
        // In case we want to finish transaction in same call we pass $finalize_params['redirect_now'] to true and SDK
        // will try to also make the redirect automatically (if headers not sent already)
        $return_arr['finalize_result'] = S2P_SDK_Method::default_finalize_result();

        try
        {
            /** @var S2P_SDK_API $api */
            if( !($api = self::get_instance( 'S2P_SDK_API', $api_parameters, $singleton )) )
            {
                if( !self::st_has_error() )
                    self::st_set_error( self::ERR_API_QUICK_CALL, self::s2p_t( 'Failed initializing API object.' ) );

                return false;
            }

            if( !($return_arr['call_details'] = $api->do_call( $call_params )) )
            {
                if( !$api->has_error() )
                    self::st_set_error( self::ERR_API_QUICK_CALL, self::s2p_t( 'Failed initializing API object.' ) );
                else
                    self::st_copy_error( $api );

                return false;
            }

            if( !($return_arr['call_result'] = $api->get_result()) )
            {
                if( !$api->has_error() )
                    self::st_set_error( self::ERR_API_QUICK_CALL, self::s2p_t( 'Failed obtaining API call result.' ) );
                else
                    self::st_copy_error( $api );

                return false;
            }

            // You should call $api->do_finalize() before sending headers if you want to be redirected to payment page...
            if( !($return_arr['finalize_result'] = $api->do_finalize( $finalize_params )) )
            {
                if( !$api->has_error() )
                    self::st_set_error( self::ERR_API_QUICK_CALL, self::s2p_t( 'Failed finalizing transaction after API call.' ) );
                else
                    self::st_copy_error( $api );

                return false;
            }

            $return_arr['call_microseconds'] = $api->get_call_time();
        } catch( \Exception $ex )
        {
            self::st_set_error( self::ERR_API_QUICK_CALL, self::s2p_t( 'Call error: [%s].', $ex->getMessage() ) );
            return false;
        }

        return $return_arr;
    }

    /**
     * Validates a hook name and returns valid value or false if hook name is not valid.
     *
     * @param string $hook_name
     *
     * @return bool|string Valid hook name or false if hook_name is not valid.
     */
    public static function prepare_hook_name( $hook_name )
    {
        if( !is_string( $hook_name )
         or !($hook_name = strtolower( trim( $hook_name ) )) )
            return false;

        return $hook_name;
    }

    /**
     * Adds a hook in call queue. When a hook is fired, script will call each callback function in order of their
     * priority. Along with standard hook parameters (check each hook definition to see which are these) you can add
     * extra parameters which you pass at hook definition
     *
     * @param string $hook_name             Hook name
     * @param callback $hook_callback       Method/Function to be called
     * @param null|array $hook_extra_args   Extra arguments to be passed when hook is fired
     * @param bool $chained_hook            If true result of hook call will overwrite parameters of next hook callback (can be used as filters)
     * @param int $priority                 Order in which hooks are fired is given by $priority parameter
     *
     * @return bool                     True if hook was added with success or false otherwise
     */
    public static function register_hook( $hook_name, $hook_callback = null, $hook_extra_args = null, $chained_hook = false, $priority = 10 )
    {
        self::st_reset_error();

        if( !($hook_name = self::prepare_hook_name( $hook_name )) )
        {
            self::st_set_error( self::ERR_HOOK_REGISTRATION, self::s2p_t( 'Please provide a valid hook name.' ) );
            return false;
        }

        if( !is_null( $hook_callback ) and !is_callable( $hook_callback ) )
        {
            self::st_set_error( self::ERR_HOOK_REGISTRATION, self::s2p_t( 'Couldn\'t add callback for hook %s.', $hook_name ) );
            return false;
        }

        $hookdata = array();
        $hookdata['callback'] = $hook_callback;
        $hookdata['args'] = $hook_extra_args;
        $hookdata['chained'] = (!empty( $chained_hook )?true:false);

        self::$hooks[$hook_name][$priority][] = $hookdata;

        ksort( self::$hooks[$hook_name], SORT_NUMERIC );

        return true;
    }

    public function unregister_hooks( $hook_name )
    {
        if( !($hook_name = self::prepare_hook_name( $hook_name ))
         or !isset( self::$hooks[$hook_name] ) )
            return false;

        unset( self::$hooks[$hook_name] );

        return true;
    }

    public static function trigger_hooks( $hook_name, array $hook_args = array() )
    {
        if( !($hook_name = self::prepare_hook_name( $hook_name ))
         or empty( self::$hooks[$hook_name] ) or !is_array( self::$hooks[$hook_name] ) )
            return null;

        if( empty( $hook_args ) or !is_array( $hook_args ) )
            $hook_args = array();

        foreach( self::$hooks[$hook_name] as $priority => $hooks_array )
        {
            if( empty( $hooks_array ) or !is_array( $hooks_array ) )
                continue;

            foreach( $hooks_array as $hook_callback )
            {
                if( empty( $hook_callback ) or !is_array( $hook_callback )
                 or empty( $hook_callback['callback'] ) )
                    continue;

                if( empty( $hook_callback['args'] ) )
                    $hook_callback['args'] = array();

                $call_hook_args = array_merge( $hook_callback['args'], $hook_args );

                $result = @call_user_func( $hook_callback['callback'], $call_hook_args );

                if( !empty( $hook_callback['chained'] )
                and is_array( $result ) )
                    $hook_args = array_merge( $hook_args, $result );
            }
        }

        // Return final hook arguments as result of hook calls
        return $hook_args;
    }

    public static function try_autoloading( $module )
    {
        $module_lower = strtolower( $module );
        if( empty( $module )
         or strpos( $module_lower, '.' ) !== false
         or strpos( $module_lower, '/' ) !== false
         or strpos( $module_lower, 's2p_sdk_' ) !== 0
         or $module_lower === 's2p_sdk_module' )
        {
            self::st_set_error( self::ERR_STATIC_INSTANCE,
                                    self::s2p_t( 'Autoloading unknown module.' ),
                                    sprintf( 'Autoloading unknown module [%s]', (!empty( $module )?$module:'???') ) );
            return false;
        }

        // Autoloading methods
        if( strpos( $module_lower, 's2p_sdk_meth_' ) === 0 )
        {
            if( !@file_exists( S2P_SDK_DIR_METHODS.$module.'.php' ) )
            {
                self::st_set_error( self::ERR_STATIC_INSTANCE,
                                        self::s2p_t( 'Module file not found.' ),
                                        sprintf( 'Module file not found [%s]', S2P_SDK_DIR_METHODS.$module_lower.'.php' ) );
                return false;
            }

            include_once( S2P_SDK_DIR_METHODS.$module.'.php' );

            if( !class_exists( 'S2P_SDK\\'.$module, false ) )
            {
                self::st_set_error( self::ERR_STATIC_INSTANCE,
                                        self::s2p_t( 'Class not found after autoloading.' ),
                                        sprintf( 'Class not found after autoloading [%s]', $module ) );
                return false;
            }

            return true;
        }

        // Fallback on "normal" classes
        if( @file_exists( S2P_SDK_DIR_CLASSES.$module.'.php' ) )
        {
            include_once( S2P_SDK_DIR_CLASSES.$module.'.php' );

            if( !class_exists( 'S2P_SDK\\'.$module, false ) )
            {
                self::st_set_error( self::ERR_STATIC_INSTANCE,
                                        self::s2p_t( 'Class not found after autoloading.' ),
                                        sprintf( 'Class not found after autoloading [%s]', $module ) );
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Initiate an instance of S2P_SDK module (all modules class names should start with S2P_SDK_{camelcase_module_name} and file name should follow case of class name
     * S2P_SDK_{lowercase_module_name}.php
     *
     * @param string $module Module name (eg. S2P_SDK_Meth_Payments)
     * @param array $module_params Parameters to be sent to init() method of module
     * @param bool $singleton Tells if returning object should be initialized as signleton
     *
     * @return bool|S2P_SDK_Module
     */
    public static function get_instance( $module = null, $module_params = null, $singleton = true )
    {
        self::st_reset_error();

        if( $module === null )
            $module = get_called_class();

        if( empty( $module )
         or stripos( $module, 's2p_sdk_' ) !== 0
         or $module === 'S2P_SDK_Module' )
        {
            self::st_set_error( self::ERR_STATIC_INSTANCE,
                                    self::s2p_t( 'Unknown module' ),
                                    sprintf( 'Unknown module [%s]', (!empty( $module )?$module:'???') ) );
            return false;
        }

        if( !class_exists( 'S2P_SDK\\'.$module, false ) )
        {
            if( !self::try_autoloading( $module ) )
            {
                if( !self::st_has_error() )
                    self::st_set_error( self::ERR_STATIC_INSTANCE,
                                            self::s2p_t( 'Unknown module' ),
                                            sprintf( 'Unknown module [%s]', (!empty( $module )?$module:'???') ) );

                return false;
            }
        }

        $module_with_namespace = 'S2P_SDK\\' . $module;

        if( !empty( $singleton )
        and isset( self::$instances[ $module ] ) )
            $module_instance = self::$instances[ $module ];
        else
            $module_instance = new $module_with_namespace( null );

        if( !($module_instance instanceof \S2P_SDK\S2P_SDK_Module) )
        {
            self::st_set_error( self::ERR_STATIC_INSTANCE,
                                    self::s2p_t( 'Module doesn\'t appear to be a Smart2Pay module.' ),
                                    sprintf( 'Module doesn\'t appear to be a Smart2Pay module. [%s]', ( ! empty( $module ) ? $module : '???' ) ) );
            return false;
        }

        $module_instance->debugging_mode( self::st_debugging_mode() );

        /** @var \S2P_SDK\S2P_SDK_Module $module_instance */
        if( $module_params !== null )
            $init_result = $module_instance->module_init( $module_params );
        else
            $init_result = $module_instance->module_init();

        if( $init_result === false )
        {
            if( $module_instance->has_error() )
                self::st_copy_error( $module_instance );
            else
                self::st_set_error( self::ERR_STATIC_INSTANCE,
                                        self::s2p_t( 'Module initialization failed.' ),
                                        sprintf( 'Module initialization failed [%s]', ( ! empty( $module ) ? $module : '???' ) ) );

            return false;
        }

        if( !empty( $singleton ) )
        {
            self::$instances[$module] = $module_instance;

            return self::$instances[$module];
        }

        return $module_instance;
    }

}
