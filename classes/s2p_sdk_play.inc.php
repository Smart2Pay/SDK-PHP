<?php

namespace S2P_SDK;

class S2P_SDK_Play extends S2P_SDK_Module
{
    private $_method = null;
    private $_func = null;

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

    public function extract_context()
    {

    }

    public function play()
    {
        if( ($method_files_arr = @glob( S2P_SDK_DIR_METHODS.'s2p_sdk_meth_*.inc.php' )) )
        {

        }

    }

    private function route_request()
    {

    }

}