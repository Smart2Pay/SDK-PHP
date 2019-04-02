<?php

namespace S2P_SDK;

class S2P_SDK_Error
{
    const ERR_OK = 0;

    //! Error code as integer
    /** @var int $error_no */
    private $error_no = self::ERR_OK;
    //! Contains error message including debugging information
    /** @var string $error_msg */
    private $error_msg = '';
    //! Contains only error message
    /** @var string $error_simple_msg */
    private $error_simple_msg = '';
    //! Contains a debugging error message
    /** @var string $error_debug_msg */
    private $error_debug_msg = '';

    //! Warnings count
    /** @var int $warnings_no */
    private $warnings_no = 0;
    //! Warning messages as array. Warnings are categorized by tags saved as array keys
    /** @var array $warnings_arr */
    private $warnings_arr = array();

    //! If true SDK will automatically throw errors in set_error() method
    /** @var bool $throw_errors */
    private $throw_errors = false;

    //! If true SDK will not throw any errors at all
    /** @var bool $prevent_throwing_errors */
    private $prevent_throwing_errors = true;

    //! Tells if SDK in is debugging mode
    /** @var bool $debugging_mode */
    private $debugging_mode = false;

    //! Tells if SDK should display detailed errors (including backtrace)
    /** @var bool $detailed_errors */
    private $detailed_errors = false;

    function __construct( $error_no = self::ERR_OK, $error_msg = '', $error_debug_msg = '', $static_instance = false )
    {
        $error_no = intval( $error_no );
        $error_msg = trim( $error_msg );

        $this->error_no = $error_no;
        $this->error_msg = $error_msg;
        $this->error_debug_msg = $error_debug_msg;

        // Make sure we inherit debugging mode from static call...
        if( empty( $static_instance ) )
        {
            $this->throw_errors( self::st_throw_errors() );
            $this->debugging_mode( self::st_debugging_mode() );
            $this->detailed_errors( self::st_detailed_errors() );
            $this->prevent_throwing_errors( self::st_prevent_throwing_errors() );
        }
    }

    /**
     * Throw exception with error code and error message only if there is an error code diffrent than self::ERR_OK
     *
     * @return bool
     */
    public function throw_error()
    {
        if( $this->error_no == self::ERR_OK
         or $this->prevent_throwing_errors() )
            return false;

        if( $this->debugging_mode() )
            throw new \Exception( $this->error_debug_msg.":\n".$this->error_msg, $this->error_no );
        else
            throw new \Exception( $this->error_simple_msg, $this->error_no );
    }

    /**
     * Throw exception with error code and error message only if there is an error code diffrent than self::ERR_OK
     *
     * @return bool
     * @throws \Exception
     */
    public static function st_throw_error()
    {
        $error_instance = self::get_error_static_instance();
        if( (($error_arr = $error_instance->get_error()) and $error_arr['error_no'] == self::ERR_OK)
         or self::st_prevent_throwing_errors() )
            return false;

        if( self::st_debugging_mode() )
            throw new \Exception( $error_arr['error_msg'], $error_arr['error_no'] );
        else
            throw new \Exception( $error_arr['error_simple_msg'], $error_arr['error_no'] );
    }

    //! Tells if we have an error
    /**
     *   Tells if current error is different than default error code provided in constructor meaning there is an error.
     *
     *   @return bool True if there is an error, false if no error
     **/
    public function has_error()
    {
        return ($this->error_no != self::ERR_OK);
    }

    public static function st_has_error()
    {
        return self::get_error_static_instance()->has_error();
    }

    //! Get number of warnings
    /**
     *   Method returns number of warnings warnings (for specified tag or as total)
     *
     *   @param string|bool $tag Check if we have warnings for provided tag (false by default)
     *   @return int Return warnings number (for specified tag or as total)
     **/
    public function has_warnings( $tag = false )
    {
        if( $tag === false )
            return $this->warnings_no;
        elseif( isset( $this->warnings_arr[$tag] ) and is_array( $this->warnings_arr[$tag] ) )
            return count( $this->warnings_arr[$tag] );

        return 0;
    }

    public static function st_has_warnings( $tag = false )
    {
        return self::get_error_static_instance()->has_warnings( $tag );
    }

    public static function mixed_to_string( $value )
    {
        if( is_bool( $value ) )
            return '('.gettype( $value ).') ['.($value?'true':'false').']';

        if( is_resource( $value ) )
            return '('.@get_resource_type( $value ).')';

        if( is_array( $value ) )
            return '(array) ['.count( $value ).']';

        if( !is_object( $value ) )
        {
            $return_str = '(' . gettype( $value ) . ') [';
            if( is_string( $value ) and strlen( $value ) > 100 )
                $return_str .= substr( $value, 0, 100 ) . '[...]';
            else
                $return_str .= $value;

            $return_str .= ']';

            return  $return_str;
        }

        return '('.@get_class( $value ).')';
    }

    //! Set error code and error message
    /**
     *   Set an error code and error message. Also method will make a backtrace of this call and present all functions/methods called (with their parameters) and files/line of call.
     *
     * @param int $error_no Error code
     * @param string $error_msg Error message
     * @param string $error_debug_msg Error message
     * @param bool|array $params Error message
     **/
    public function set_error( $error_no, $error_msg, $error_debug_msg = '', $params = false )
    {
        if( empty( $params ) or !is_array( $params ) )
            $params = array();

        if( empty( $params['prevent_throwing_errors'] ) )
            $params['prevent_throwing_errors'] = false;

        $backtrace = '';
        if( is_array( ($err_info = debug_backtrace()) ) )
        {
            $lvl = count( $err_info );
            for( $i = $lvl-1; $i >= 0; $i-- )
            {
                $args_str = '';
                if( is_array( $err_info[$i]['args'] ) )
                {
                    foreach( $err_info[$i]['args'] as $key => $val )
                        $args_str .= self::mixed_to_string( $val ).', ';

                    $args_str = substr( $args_str, 0, -2 );
                } else
                    $args_str = $err_info[$i]['args'];

                if( !isset( $err_info[$i]['class'] ) )
                    $err_info[$i]['class'] = '';
                if( !isset( $err_info[$i]['type'] ) )
                    $err_info[$i]['type'] = '';
                if( !isset( $err_info[$i]['function'] ) )
                    $err_info[$i]['function'] = '';
                if( !isset( $err_info[$i]['file'] ) )
                    $err_info[$i]['file'] = '(unknown)';
                if( !isset( $err_info[$i]['line'] ) )
                    $err_info[$i]['line'] = '-1';

                $backtrace .= '#'.($lvl-$i).'. '.$err_info[$i]['class'].$err_info[$i]['type'].$err_info[$i]['function'].'( '.$args_str.' ) -> '.
                              $err_info[$i]['file'].':'.$err_info[$i]['line']."\n";
            }
        }

        $this->error_no = $error_no;
        $this->error_simple_msg = $error_msg;
        if( $error_debug_msg != '' )
            $this->error_debug_msg = $error_debug_msg;
        else
            $this->error_debug_msg = $error_msg;
        $this->error_msg = '<pre>Error: ('.$this->error_debug_msg.')'."\n".
                           'Code: ('.$error_no.')'."\n".
                           'Backtrace:'."\n".
                           $backtrace.'</pre>';

        if( empty( $params['prevent_throwing_errors'] )
        and $this->throw_errors() )
            $this->throw_error();
    }

    public static function st_set_error( $error_no, $error_msg, $error_debug_msg = '' )
    {
        self::get_error_static_instance()->set_error( $error_no, $error_msg, $error_debug_msg );
    }

    //! Add a warning message
    /**
     *   Add a warning message for a speficied tag or as general warning. Also method will make a backtrace of this call and present all functions/methods called (with their parameters) and files/line of call.
     *
     *   @param string $warning Warning message
     *   @param bool|string $tag Add warning for a specific tag (default false). If this is not provided warning will be added as general warning.
     **/
    public function add_warning( $warning, $tag = false )
    {
        $backtrace = '';
        if( is_array( ($err_info = debug_backtrace()) ) )
        {
            $lvl = count( $err_info );
            for( $i = $lvl-1; $i >= 0; $i-- )
            {
                $args_str = '';
                if( is_array( $err_info[$i]['args'] ) )
                {
                    foreach( $err_info[$i]['args'] as $key => $val )
                        $args_str .= self::mixed_to_string( $val ).', ';

                    $args_str = substr( $args_str, 0, -2 );
                } else
                    $args_str = $err_info[$i]['args'];

                $backtrace .= '#'.($lvl-$i).'. '.$err_info[$i]['class'].$err_info[$i]['type'].$err_info[$i]['function'].'( '.$args_str.' ) -> '.
                              $err_info[$i]['file'].':'.$err_info[$i]['line']."\n";
            }
        }

        if( !empty( $tag ) )
        {
            if( !isset( $this->warnings_arr[$tag] ) )
                $this->warnings_arr[$tag] = array();

            $this->warnings_arr[$tag][] = $warning."\n".
                                      'Backtrace:'."\n".
                                      $backtrace;
        } else
            $this->warnings_arr[] = $warning."\n".
                                'Backtrace:'."\n".
                                $backtrace;

        $this->warnings_no++;
    }

    public static function st_add_warning( $warning, $tag = false )
    {
        self::get_error_static_instance()->add_warning( $warning, $tag );
    }

    //! Remove warnings
    /**
     * Remove warning messages for a speficied tag or all warnings.
     *
     * @param string|bool $tag Remove warnings of specific tag or all warnings. (default false)
     *
     * @return int Returns number of warnings remaining (if any)
     **/
    public function reset_warnings( $tag = false )
    {
        if( $tag !== false )
        {
            if( isset( $this->warnings_arr[$tag] ) and is_array( $this->warnings_arr[$tag] ) )
            {
                $this->warnings_no -= count( $this->warnings_arr[$tag] );
                unset( $this->warnings_arr[$tag] );

                if( !$this->warnings_no )
                    $this->warnings_arr = array();
            }
        } else
        {
            $this->warnings_arr = array();
            $this->warnings_no = 0;
        }

        return $this->warnings_no;
    }

    public static function st_reset_warnings( $tag = false )
    {
        return self::get_error_static_instance()->reset_warnings( $tag );
    }

    public function reset_error()
    {
        $this->error_no = self::ERR_OK;
        $this->error_msg = '';
        $this->error_simple_msg = '';
        $this->error_debug_msg = '';
    }

    public static function st_reset_error()
    {
        self::get_error_static_instance()->reset_error();
    }

    //! Get error details
    /**
     *   Method returns an array with current error code and message.
     *
     *   @return array Array with indexes 'error_no' for error code and 'error_msg' for error message
     **/
    public function get_error()
    {
        $return_arr = array(
            'error_no' => $this->error_no,
            'error_msg' => $this->error_msg,
            'error_simple_msg' => $this->error_simple_msg,
            'error_debug_msg' => $this->error_debug_msg,
        );

        if( $this->debugging_mode() )
        {
            if( $this->detailed_errors() )
                $return_arr['display_error'] = $this->error_msg;
            else
                $return_arr['display_error'] = $this->error_debug_msg;
        } else
            $return_arr['display_error'] = $this->error_simple_msg;

        return $return_arr;
    }

    /**
     * @return string Returns error message
     */
    public function get_error_message()
    {
        if( $this->debugging_mode() )
            return $this->error_debug_msg;

        return $this->error_simple_msg;
    }

    /**
     * @return int Returns error code
     */
    public function get_error_code()
    {
        return $this->error_no;
    }

    /**
     * Copies error set in $obj to current object
     *
     * @param S2P_SDK_Error $obj
     *
     * @return bool
     */
    public function copy_error( $obj )
    {
        if( empty( $obj ) or !($obj instanceof S2P_SDK_Error)
         or !($error_arr = $obj->get_error())
         or !is_array( $error_arr ) )
            return false;

        $this->error_no = $error_arr['error_no'];
        $this->error_msg = $error_arr['error_msg'];
        $this->error_simple_msg = $error_arr['error_simple_msg'];
        $this->error_debug_msg = $error_arr['error_debug_msg'];

        return true;
    }

    public static function st_copy_error( $obj )
    {
        return self::get_error_static_instance()->copy_error( $obj );
    }

    /**
     * Copies error set in $error_arr array to current object
     *
     * @param array $error_arr
     *
     * @return bool
     */
    public function copy_error_from_array( $error_arr )
    {
        if( empty( $error_arr ) or !is_array( $error_arr )
         or !isset( $error_arr['error_no'] ) or !isset( $error_arr['error_msg'] )
         or !isset( $error_arr['error_simple_msg'] ) or !isset( $error_arr['error_debug_msg'] ) )
            return false;

        $this->error_no = $error_arr['error_no'];
        $this->error_msg = $error_arr['error_msg'];
        $this->error_simple_msg = $error_arr['error_simple_msg'];
        $this->error_debug_msg = $error_arr['error_debug_msg'];

        return true;
    }

    public static function st_copy_error_from_array( $error_arr )
    {
        return self::get_error_static_instance()->copy_error_from_array( $error_arr );
    }

    public function copy_static_error()
    {
        return $this->copy_error( self::get_error_static_instance() );
    }

    public static function st_get_error()
    {
        return self::get_error_static_instance()->get_error();
    }

    public static function st_get_error_code()
    {
        return self::get_error_static_instance()->get_error_code();
    }

    public static function st_get_error_message()
    {
        return self::get_error_static_instance()->get_error_message();
    }

    //! Return warnings for specified tag or all warnings
    /**
     * Return warnings array for specified tag (if any) or
     *
     * @param string|bool $tag Check if we have warnings for provided tag (false by default)
     * @return array|bool Return array of warnings (all or for specified tag) or false if no warnings
     **/
    public function get_warnings( $tag = false )
    {
        if( empty( $this->warnings_arr )
         or ($tag !== false and !isset( $this->warnings_arr[$tag] )) )
            return false;

        $ret_warnings = array();

        if( $tag === false )
            $warning_pool = $this->warnings_arr;
        else
            $warning_pool = $this->warnings_arr[$tag];

        if( empty( $warning_pool ) or !is_array( $warning_pool ) )
            $warning_pool = array();

        foreach( $warning_pool as $wtag => $warning )
        {
            if( is_array( $warning ) )
            {
                foreach( $warning as $junk => $value )
                    $ret_warnings[] = '['.$wtag.'] '.$value;
            } else
                $ret_warnings[] = $warning;
        }

        return $ret_warnings;
    }

    public static function st_get_warnings( $tag = false )
    {
        return self::get_error_static_instance()->get_warnings( $tag );
    }

    //! \brief Returns function/method call backtrace
    /**
     *  Used for debugging calls to functions or methods.
     *  @return string Method will return a string representing function/method calls.
     */
    public function debug_call_backtrace()
    {
        $backtrace = '';
        if( is_array( ($err_info = debug_backtrace()) ) )
        {
            $err_info = array_reverse( $err_info );
            foreach( $err_info as $i => $trace_data )
            {
                if( !isset( $trace_data['args'] ) )
                    $trace_data['args'] = '';
                if( !isset( $trace_data['class'] ) )
                    $trace_data['class'] = '';
                if( !isset( $trace_data['type'] ) )
                    $trace_data['type'] = '';
                if( !isset( $trace_data['function'] ) )
                    $trace_data['function'] = '';
                if( !isset( $trace_data['file'] ) )
                    $trace_data['file'] = '(unknown)';
                if( !isset( $trace_data['line'] ) )
                    $trace_data['line'] = 0;

                $args_str = '';
                if( is_array( $trace_data['args'] ) )
                {
                    foreach( $trace_data['args'] as $key => $val )
                        $args_str .= self::mixed_to_string( $val ).', ';

                    $args_str = substr( $args_str, 0, -2 );
                } else
                    $args_str = $trace_data['args'];

                $backtrace .= '#'.($i+1).'. '.$trace_data['class'].$trace_data['type'].$trace_data['function'].'( '.$args_str.' ) - '.
                              $trace_data['file'].':'.$trace_data['line']."\n";
            }
        }

        return $backtrace;
    }

    //! @brief Returns function/method call backtrace. Used for static calls
    /**
     *  Used for debugging calls to functions or methods.
     *  @return string Method will return a string representing function/method calls.
     */
    public static function st_debug_call_backtrace()
    {
        $backtrace = '';
        if( is_array( ($err_info = debug_backtrace()) ) )
        {
            $err_info = array_reverse( $err_info );
            foreach( $err_info as $i => $trace_data )
            {
                if( !isset( $trace_data['args'] ) )
                    $trace_data['args'] = '';
                if( !isset( $trace_data['class'] ) )
                    $trace_data['class'] = '';
                if( !isset( $trace_data['type'] ) )
                    $trace_data['type'] = '';
                if( !isset( $trace_data['function'] ) )
                    $trace_data['function'] = '';
                if( !isset( $trace_data['file'] ) )
                    $trace_data['file'] = '(unknown)';
                if( !isset( $trace_data['line'] ) )
                    $trace_data['line'] = 0;

                $args_str = '';
                if( is_array( $trace_data['args'] ) )
                {
                    foreach( $trace_data['args'] as $key => $val )
                        $args_str .= self::mixed_to_string( $val ).', ';

                    $args_str = substr( $args_str, 0, -2 );
                } else
                    $args_str = $trace_data['args'];

                $backtrace .= '#'.($i+1).'. '.$trace_data['class'].$trace_data['type'].$trace_data['function'].'( '.$args_str.' ) - '.
                              $trace_data['file'].':'.$trace_data['line']."\n";
            }
        }

        return $backtrace;
    }

    public function throw_errors( $mode = null )
    {
        if( is_null( $mode ) )
            return $this->throw_errors;

        $this->throw_errors = (!empty( $mode )?true:false);

        return $this->throw_errors;
    }

    public static function st_throw_errors( $mode = null )
    {
        return self::get_error_static_instance()->throw_errors( $mode );
    }

    public function prevent_throwing_errors( $mode = null )
    {
        if( is_null( $mode ) )
            return $this->prevent_throwing_errors;

        $this->prevent_throwing_errors = (!empty( $mode )?true:false);

        return $this->prevent_throwing_errors;
    }

    public static function st_prevent_throwing_errors( $mode = null )
    {
        return self::get_error_static_instance()->prevent_throwing_errors( $mode );
    }

    public function debugging_mode( $mode = null )
    {
        if( is_null( $mode ) )
            return $this->debugging_mode;

        $this->debugging_mode = (!empty( $mode )?true:false);

        return $this->debugging_mode;
    }

    public static function st_debugging_mode( $mode = null )
    {
        return self::get_error_static_instance()->debugging_mode( $mode );
    }

    public function detailed_errors( $mode = null )
    {
        if( is_null( $mode ) )
            return $this->detailed_errors;

        $this->detailed_errors = (!empty( $mode )?true:false);

        return $this->detailed_errors;
    }

    public static function st_detailed_errors( $mode = null )
    {
        return self::get_error_static_instance()->detailed_errors( $mode );
    }

    static function get_error_static_instance()
    {
        static $error_instance = false;

        if( empty( $error_instance ) )
            $error_instance = new \S2P_SDK\S2P_SDK_Error( self::ERR_OK, '', '', true );

        return $error_instance;
    }
}
