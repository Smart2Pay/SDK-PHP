<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 16.03.2015
 * Time: 14:30
 */

namespace S2P_SDK;

class S2P_SDK_Language extends S2P_SDK_Error
{
    const LANG_EN = 'en', LANG_RO = 'ro';

    /** @var S2P_SDK_Language_Container $lang_callable_obj */
    private static $lang_callable_obj = false;

    /**
     * Returns language class that handles translation tasks
     *
     * @return S2P_SDK_Language_Container
     */
    static function language_container()
    {
        if( empty( self::$lang_callable_obj ) )
            self::$lang_callable_obj = new S2P_SDK_Language_Container();

        return self::$lang_callable_obj;
    }

    /**
     * @return bool Returns true if multi language is enabled or false otherwise
     */
    public static function get_multi_language_enabled()
    {
        return self::language_container()->get_multi_language_enabled();
    }

    /**
     * @param bool $enabled Whether multi language should be enabled or not
     * @return bool Returns multi language enabled value currently set
     */
    public static function set_multi_language( $enabled )
    {
        return self::language_container()->set_multi_language( $enabled );
    }

    /**
     * @return string Returns currently selected language
     */
    public static function get_current_language()
    {
        return self::language_container()->get_current_language();
    }

    /**
     * @param string $lang ISO 2 chars (lowercase) language code to add language files to
     * @param array $files_arr Array with language files to be added
     *
     * @return bool Returns true on success or false on falure
     */
    public static function add_language_files( $lang, $files_arr )
    {
        return self::language_container()->add_language_files( $lang, $files_arr );
    }

    /**
     * Define a language used by SDK
     *
     * @param string $lang ISO 2 chars (lowercase) language code
     * @param array $lang_params Language details
     *
     * @return bool True if adding language was successful, false otherwise
     */
    public static function define_language( $lang, array $lang_params )
    {
        return self::language_container()->define_language( $lang, $lang_params );
    }

    /**
     * Translate a specific text in currently selected language. This method receives a variable number of parameters in same way as sprintf works.
     * @param string $index Language index to be translated
     *
     * @return string Translated string
     */
    public static function s2p_t( $index )
    {
        $numargs = func_num_args();
        $arg_list = func_get_args();

        if( $numargs > 1 )
            @array_shift( $arg_list );
        else
            $arg_list = array();

        return self::language_container()->s2p_t( $index, $arg_list );
    }

    /**
     * Translate a text into a specific language. This method receives a variable number of parameters in same way as sprintf works.
     *
     * @param string $index Language index to be translated
     * @param string $lang ISO 2 chars (lowercase) language code
     *
     * @return string Translated text
     */
    public static function s2p_tl( $index, $lang )
    {
        $numargs = func_num_args();
        $arg_list = func_get_args();

        if( $numargs > 2 )
        {
            @array_shift( $arg_list );
            @array_shift( $arg_list );
        } else
            $arg_list = array();

        return self::language_container()->s2p_tl( $index, $lang, $arg_list );
    }
}
