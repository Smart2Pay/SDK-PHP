<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_CLASSES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_method.inc.php' );

abstract class S2P_SDK_Module extends S2P_SDK_Language
{
    const ERR_HOOK_REGISTRATION = 1000, ERR_STATIC_INSTANCE = 1001, ERR_API_QUICK_CALL = 1002;

    const VERSION = '1.0.0';

    private static $instances = array();
    private static $hooks = array();

    private static $one_call_settings = array(
        'api_key' => '',
        'site_id' => 0,
        'environment' => '',
        'return_url' => '',
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

    function __construct( $module_params = false )
    {
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

        return $return_arr;
    }

    /**
     * Entry point when making Smart2Pay API calls
     *
     * @param array $api_parameters Parameters passed to API object which contains request details
     * @param array|false $call_params Additional parameters sent to S2P_SDK\S2P_SDK_API::do_call()
     * @param array|false $finalize_params Array with parameters sent to
     *
     * @return array|false Returns false on error (error available with S2P_SDK\S2P_SDK_Module::st_get_error())
     *
     * @see S2P_SDK\S2P_SDK_Module::st_get_error()
     * @see S2P_SDK\S2P_SDK_API::do_call()
     */
    public static function quick_call( $api_parameters, $call_params = false, $finalize_params = false )
    {
        self::st_reset_error();

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
            if( !($api = self::get_instance( 'S2P_SDK_API', $api_parameters )) )
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
         or strstr( $module_lower, '.' ) !== false
         or strstr( $module_lower, '/' ) !== false
         or substr( $module_lower, 0, 8 ) != 's2p_sdk_'
         or $module_lower == 's2p_sdk_module' )
        {
            self::st_set_error( self::ERR_STATIC_INSTANCE,
                                    self::s2p_t( 'Autoloading unknown module.' ),
                                    sprintf( 'Autoloading unknown module [%s]', (!empty( $module )?$module:'???') ) );
            return false;
        }

        // Autoloading methods
        if( substr( $module_lower, 0, 13 ) == 's2p_sdk_meth_' )
        {
            if( !@file_exists( S2P_SDK_DIR_METHODS.$module_lower.'.inc.php' ) )
            {
                self::st_set_error( self::ERR_STATIC_INSTANCE,
                                        self::s2p_t( 'Module file not found.' ),
                                        sprintf( 'Module file not found [%s]', S2P_SDK_DIR_METHODS.$module_lower.'.inc.php' ) );
                return false;
            }

            include_once( S2P_SDK_DIR_METHODS.$module_lower.'.inc.php' );

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
        if( @file_exists( S2P_SDK_DIR_CLASSES.$module_lower.'.inc.php' ) )
        {
            include_once( S2P_SDK_DIR_CLASSES.$module_lower.'.inc.php' );

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
     * Initiate an instance of S2P_SDK module (all modules class names should start with S2P_SDK_{camelcase_module_name} and file name should be lower case
     * and should start with s2p_sdk_{lowercase_module_name}.inc.php
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

        if( is_null( $module ) )
            $module = get_called_class();

        if( empty( $module )
         or strtolower( substr( $module, 0, 8 ) ) != 's2p_sdk_'
         or $module == 'S2P_SDK_Module' )
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
        if( ! is_null( $module_params ) )
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
            self::$instances[ $module ] = $module_instance;

            return self::$instances[ $module ];
        }

        return $module_instance;
    }

}
