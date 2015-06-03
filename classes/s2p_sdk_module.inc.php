<?php

namespace S2P_SDK;

abstract class S2P_SDK_Module extends S2P_SDK_Language
{
    const ERR_HOOK_REGISTRATION = 1;

    const VERSION = '1.0.0';

    private static $instances = array();
    private static $hooks = array();

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed
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

    public function module_init( $module_params = false )
    {
        $this->init( $module_params );
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

    public static function get_instance( $module = null, $module_params = null, $singleton = true )
    {
        if( is_null( $module ) )
            $module = get_called_class();

        if( ! class_exists( $module, false )
            or strtolower( substr( $module, 0, 8 ) ) != 's2p_sdk_'
            or $module == 'S2P_SDK_Module'
        )
            return false;

        if( !empty( $singleton )
        and isset( self::$instances[ $module ] ) )
            return self::$instances[ $module ];

        $module_instance = new $module();

        if( !($module_instance instanceof \S2P_SDK\S2P_SDK_Module) )
            return false;

        /** @var \S2P_SDK\S2P_SDK_Module $module_instance */
        if( ! is_null( $module_params ) )
            $module_instance->module_init( $module_params );
        else
            $module_instance->module_init();

        if( !empty( $singleton ) )
        {
            self::$instances[ $module ] = $module_instance;

            return self::$instances[ $module ];
        }

        return $module_instance;
    }

}
