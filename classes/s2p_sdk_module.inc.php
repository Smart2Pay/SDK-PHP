<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_METHODS' ) or !defined( 'S2P_SDK_DIR_CLASSES' ) )
    die( 'Something went wrong.' );

abstract class S2P_SDK_Module extends S2P_SDK_Language
{
    const ERR_HOOK_REGISTRATION = 1000, ERR_STATIC_INSTANCE = 1001;

    const VERSION = '1.0.0';

    private static $instances = array();
    private static $hooks = array();

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

    function __construct()
    {
        parent::__construct();
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

                $result = call_user_func_array( $hook_callback['callback'], $call_hook_args );

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
                                    self::s2p_t( 'Autoloading unknown module' ),
                                    sprintf( 'Autoloading unknown module [%s]', (!empty( $module )?$module:'???') ) );
            return false;
        }

        // Autoloading methods
        if( substr( $module_lower, 0, 13 ) == 's2p_sdk_meth_' )
        {
            if( !@file_exists( S2P_SDK_DIR_METHODS.$module_lower.'.inc.php' ) )
            {
                self::st_set_error( self::ERR_STATIC_INSTANCE,
                                        self::s2p_t( 'Module file not found' ),
                                        sprintf( 'Module file not found [%s]', S2P_SDK_DIR_METHODS.$module_lower.'.inc.php' ) );
                return false;
            }

            include_once( S2P_SDK_DIR_METHODS.$module_lower.'.inc.php' );

            if( !class_exists( 'S2P_SDK\\'.$module, false ) )
            {
                self::st_set_error( self::ERR_STATIC_INSTANCE,
                                        self::s2p_t( 'Class not found after autoloading' ),
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
                                        self::s2p_t( 'Class not found after autoloading' ),
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

        if( !empty( $singleton )
        and isset( self::$instances[ $module ] ) )
            return self::$instances[ $module ];

        $module_with_namespace = 'S2P_SDK\\'.$module;

        $module_instance = new $module_with_namespace();

        if( !($module_instance instanceof \S2P_SDK\S2P_SDK_Module) )
        {
            self::st_set_error( self::ERR_STATIC_INSTANCE,
                                    self::s2p_t( 'Module doesn\'t appear to be a Smart2Pay module.' ),
                                    sprintf( 'Module doesn\'t appear to be a Smart2Pay module. [%s]', ( ! empty( $module ) ? $module : '???' ) ) );
            return false;
        }

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
                                        self::s2p_t( 'Module initialization failed' ),
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
