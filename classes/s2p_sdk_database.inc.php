<?php

namespace S2P_SDK;

class S2P_SDK_Database extends S2P_SDK_Module
{
    const ERR_DATABASE_INTERFACE = 100;

    /** @var S2P_SDK_Database_Wrapper $db_wrapper */
    private static $db_wrapper = false;

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed If this function returns false it will consider module is not initialized correctly and will not return class instance
     */
    public function init( $module_params = false )
    {
        $this->register_hook( 'db_table_name', array( $this, 'table_names' ) );

        return true;
    }

    public static function set_db_wrapper( S2P_SDK_Database_Wrapper $wrapper )
    {

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

}
