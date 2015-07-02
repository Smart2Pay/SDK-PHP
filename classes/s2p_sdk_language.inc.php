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

    function __construct()
    {
        parent::__construct();
    }

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
     * @return string Returns currently selected language
     */
    public static function get_current_language()
    {
        return self::language_container()->get_current_language();
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

class S2P_SDK_Language_Container extends S2P_SDK_Error
{
    // We take error codes from 100000+ to let 1-99999 for custom defined constant errors
    const ERR_LANGUAGE_DEFINITION = 100000, ERR_LANGUAGE_LOAD = 100001;

    //! Fallback language in case we try to translate a text which is not defined in current language
    private static $DEFAULT_LANGUAGE = '';
    //! Current language
    private static $CURRENT_LANGUAGE = '';
    //! Contains defined language which can be used by the system. These are not necessary loaded in memory in order to optimize memory
    //! eg. $DEFINED_LANGUAGES['en'] = array( 'title' => 'English (friendly name of language)', 'files' => array( 'path_to_csv_file1', 'path_to_csv_file2' ) );
    private static $DEFINED_LANGUAGES = array();

    private static $LANGUAGE_INDEXES = array();

    function __construct()
    {
        parent::__construct();
    }

    public static function st_get_current_language()
    {
        return self::$CURRENT_LANGUAGE;
    }

    public static function st_set_current_language( $lang )
    {
        if( !self::valid_language( $lang ) )
            return false;

        self::$CURRENT_LANGUAGE = $lang;
        return self::$CURRENT_LANGUAGE;
    }

    public function get_current_language()
    {
        return self::st_get_current_language();
    }

    public function set_current_language( $lang )
    {
        return self::st_set_current_language( $lang );
    }

    static function prepare_lang_index( $lang )
    {
        return strtolower( trim( $lang ) );
    }

    /**
     *
     * Reset all indexes loaded for provided language if $lang !== false or all loaded indexes if $lang === false
     *
     * @param bool|string $lang
     *
     * @return $this
     */
    function reset_language_indexes( $lang = false )
    {
        if( $lang === false )
            self::$LANGUAGE_INDEXES = array();
        else
        {
            $lang = self::prepare_lang_index( $lang );
            if( isset( self::$LANGUAGE_INDEXES[$lang] ) )
                self::$LANGUAGE_INDEXES[$lang] = array();
        }

        return $this;
    }

    /**
     * Tells if language $lang is a valid defined language in the system
     *
     * @param string $lang
     *
     * @return bool|string
     */
    static function valid_language( $lang )
    {
        $lang = self::prepare_lang_index( $lang );
        return (isset( self::$DEFINED_LANGUAGES[$lang] )?$lang:false);
    }

    /**
     * Tells if language $lang is loaded (files were parsed and added to indexes array)
     *
     * @param $lang
     *
     * @return bool|string
     */
    static function language_loaded( $lang )
    {
        $lang = self::prepare_lang_index( $lang );
        return (isset( self::$LANGUAGE_INDEXES[$lang] )?$lang:false);
    }

    /**
     * Define a language used by SDK
     *
     * @param string $lang ISO 2 chars (lowercase) language code
     * @param array $lang_params Language details
     *
     * @return bool True if adding language was successful, false otherwise
     */
    public function define_language( $lang, array $lang_params )
    {
        $this->reset_error();

        $lang = self::prepare_lang_index( $lang );
        if( empty( $lang )
         or empty( $lang_params ) or !is_array( $lang_params )
         or empty( $lang_params['title'] )
         or empty( $lang_params['files'] ) or !is_array( $lang_params['files'] ) )
        {
            $this->set_error( self::ERR_LANGUAGE_DEFINITION, 'Please provide valid parameters for language definition.' );
            return false;
        }

        foreach( $lang_params['files'] as $lang_file )
        {
            if( empty( $lang_file )
             or !@file_exists( $lang_file ) or !@is_readable( $lang_file ) )
            {
                $this->set_error( self::ERR_LANGUAGE_DEFINITION, 'Language file ['.@basename( $lang_file ).'] for language ['.$lang.'] not found or not readable.' );
                return false;
            }
        }

        if( empty( self::$DEFINED_LANGUAGES[$lang] ) )
            self::$DEFINED_LANGUAGES[$lang] = array();

        self::$DEFINED_LANGUAGES[$lang]['title'] = trim( $lang_params['title'] );

        if( empty( self::$DEFINED_LANGUAGES[$lang]['files'] ) )
            self::$DEFINED_LANGUAGES[$lang]['files'] = array();

        self::$DEFINED_LANGUAGES[$lang]['files'] = array_merge( self::$DEFINED_LANGUAGES[$lang]['files'], $lang_params['files'] );

        return true;
    }

    /**
     * Returns language details as it was defined using self::define_language()
     *
     * @param string $lang
     *
     * @return bool|array
     */
    public static function get_defined_language( $lang )
    {
        if( !($lang = self::valid_language( $lang )) )
            return false;

        return self::$DEFINED_LANGUAGES[$lang];
    }

    /**
     * Loads provided CSV files in 'files' index of language definition array for language $lang
     *
     * @param string $lang ISO 2 chars (lowercase) language code
     *
     * @return bool True if loading was with success, false otherwise
     */
    public function load_language( $lang )
    {
        $this->reset_error();

        if( !($lang = self::valid_language( $lang ))
         or !($lang_details = self::get_defined_language( $lang ))
         or empty( $lang_details['files'] ) or !is_array( $lang_details['files'] ) )
        {
            $this->set_error( self::ERR_LANGUAGE_LOAD, 'Language ['.$lang.'] not defined.' );
            return false;
        }

        foreach( $lang_details['files'] as $file )
        {
            if( !$this->load_language_file( $file, $lang ) )
                return false;
        }

        return true;
    }

    /**
     * Loads a specific CSV file for language $lang. This file is provided in 'files' index of language definition array for provided language
     *
     * @param string $file
     * @param string $lang
     *
     * @return bool
     */
    private function load_language_file( $file, $lang )
    {
        if( !($lang = self::valid_language( $lang )) )
        {
            $this->set_error( self::ERR_LANGUAGE_LOAD, 'Language ['.$lang.'] not defined.' );
            return false;
        }

        if( !($utf8_file = self::convert_to_utf8( $file )) )
        {
            $this->set_error( self::ERR_LANGUAGE_LOAD, 'Couldn\'t convert language file ['.@basename( $file ).'], language ['.$lang.'] to UTF-8 encoding.' );
            return false;
        }

        if( !@file_exists( $utf8_file ) or !is_readable( $utf8_file )
         or !($fil = @fopen( $utf8_file, 'r' )) )
        {
            $this->set_error( self::ERR_LANGUAGE_LOAD, 'File ['.@basename( $utf8_file ).'] doesn\'t exist or is not readable for language ['.$lang.'].' );
            return false;
        }

        if( function_exists( 'mb_internal_encoding' ) )
            @mb_internal_encoding( 'UTF-8' );

        $mb_substr_exists = false;
        if( function_exists( 'mb_substr' ) )
            $mb_substr_exists = true;

        while( ($buf = @fgets( $fil )) )
        {
            if( ($mb_substr_exists and mb_substr( ltrim( $buf ), 0, 1 ) == '#')
             or (!$mb_substr_exists and substr( ltrim( $buf ), 0, 1 ) == '#') )
                continue;

            $buf = rtrim( $buf, "\r\n" );

            if( !($csv_line = @str_getcsv( $buf, ',', '"', '\\' ))
             or !is_array( $csv_line )
             or count( $csv_line ) != 2 )
                continue;

            $index = $csv_line[0];
            $index_lang = $csv_line[1];

            self::$LANGUAGE_INDEXES[$lang][$index] = $index_lang;
        }

        @fclose( $fil );

       return true;
    }

    /**
     * Given an absolute file path, this method will return file name which should contain UTF-8 encoded content of original file
     *
     * @param string $file ablsolute path of file which should be converted to UTF-8 encoding
     *
     * @return string Resulting file name which will hold UTF-8 encoded content of original file
     */
    static function get_utf8_file_name( $file )
    {
        $path_info = @pathinfo( $file );
        return $path_info['dirname'].'/'.$path_info['filename'].'-utf8.'.$path_info['extension'];
    }

    /**
     * Converts a given file to a UTF-8 encoded content.
     *
     * @param string $file ablsolute path of file which should be converted to UTF-8 encoding
     * @param bool|array $params Method parameters allows to overwrite UTF-8 encoded file name
     *
     * @return bool|string Returns absolute path of UTF-8 encoded file
     */
    static function convert_to_utf8( $file, $params = false )
    {
        if( empty( $file ) or !@file_exists( $file ) )
            return false;

        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['utf8_file'] ) )
            $params['utf8_file'] = self::get_utf8_file_name( $file );

        ob_start();
        if( !($file_mime = @system( 'file --mime-encoding '.escapeshellarg( $file ) )) )
            return false;

        if( @file_exists( $params['utf8_file'] ) )
            @unlink( $params['utf8_file'] );

        $file_mime = str_replace( $file.': ', '', $file_mime );

        if( !in_array( strtolower( $file_mime ), array( 'utf8', 'utf-8' ) ) )
        {
            if( @system( 'iconv -f ' . escapeshellarg( $file_mime ) . ' -t utf-8 ' . escapeshellarg( $file ) . ' > ' . escapeshellarg( $params['utf8_file'] ) ) === false
             or !@file_exists( $params['utf8_file'] ) )
            {
                ob_end_clean();

                return false;
            }
        } else
            @copy( $file, $params['utf8_file'] );
        ob_end_clean();

        if( !@file_exists( $params['utf8_file'] ) )
            return false;

        return $params['utf8_file'];
    }

    /**
     * Translate text $index. If $index contains %XX format (@see vsprintf), arguments will be passed in $args parameter.
     *
     * @param string $index Language index to be translated
     * @param array $args Array of arguments to be used to populate $index (@see vsprintf)
     *
     * @return string Translated string
     */
    public function s2p_t( $index, array $args = array() )
    {
        if( empty( $args ) or !is_array( $args ) )
            $args = array();

        return $this->s2p_tl( $index, self::get_current_language(), $args );
    }

    /**
     * Translate text $index for language $lang. If $index contains %XX format (@see vsprintf), arguments will be passed in $args parameter.
     *
     * @param string $index Language index to be translated
     * @param string $lang
     * @param array $args Array of arguments to be used to populate $index (@see vsprintf)
     *
     * @return string
     */
    public function s2p_tl( $index, $lang, array $args = array() )
    {
        if( !($lang = self::valid_language( $lang )) )
            return $index;

        if( empty( $args ) or !is_array( $args ) )
            $args = array();

        if( !self::language_loaded( $lang ) )
        {
            if( !$this->load_language( $lang ) )
            {
                if( ($error_arr = $this->get_error()) and !empty( $error_arr['error_simple_msg'] ) )
                    $error_msg = $error_arr['error_simple_msg'];
                else
                    $error_msg = 'Error loading language [' . $lang . ']';

                return $error_msg;
            }
        }

        if( isset( self::$LANGUAGE_INDEXES[$lang][$index] ) )
            $working_index = self::$LANGUAGE_INDEXES[$lang][$index];
        else
            $working_index = $index;

        if( !empty( $args ) )
        {
            // we should replace some %s...
            if( ($result = @vsprintf( $working_index, $args )) !== false )
                return $result;

            return $working_index.' ['.count( $args ).' args]';
        }

        return $working_index;
    }

}