<?php

namespace S2P_SDK;

class S2P_SDK_Rest_API_Request extends S2P_SDK_Language
{
    const ERR_REQUEST_INIT = 1;

    /** @var string $_url */
    private $_url = '';
    /** @var string $_body */
    private $_body = '';
    /** @var array $_get */
    private $_get = array();
    /** @var array $_post */
    private $_post = array();
    /** @var array $_headers */
    private $_headers = array();
    /** @var string $_response_buffer */
    private $_response_buffer = '';

    function __construct()
    {
        parent::__construct();
        $this->reset_api_request();
    }

    private function reset_api_request()
    {
        $this->_url = '';
        $this->_body = '';
        $this->_get = array();
        $this->_post = array();
        $this->_headers = array();
        $this->_response_buffer = '';
    }

    public function get_headers()
    {
        if( empty( $this->_headers ) or !is_array( $this->_headers ) )
            $this->_headers = array();

        return $this->_headers;
    }

    /**
     * Return last return buffer
     *
     * @return string Returns response buffer
     */
    public function get_response_buffer()
    {
        return $this->_response_buffer;
    }

    /**
     * Get current request URL
     *
     * @return string Returns request URL
     */
    public function get_url()
    {
        return $this->_url;
    }

    /**
     * Set request URL
     *
     * @param string $url
     *
     * @return bool Returns true on success or false on fail
     */
    public function set_url( $url )
    {
        if( !is_string( $url ) )
            return false;

        $this->_url = $url;
        return true;
    }

    /**
     * Get current request body
     *
     * @return string Returns request body
     */
    public function get_body()
    {
        return $this->_body;
    }

    /**
     * Set request body
     *
     * @param string $body
     *
     * @return bool Returns true on success or false on fail
     */
    public function set_body( $body )
    {
        if( !is_string( $body ) )
            return false;

        $this->_body = $body;
        return true;
    }

    /**
     * Set a header key, value pair
     *
     * @param string $key
     * @param string $value
     *
     * @return bool Returns true on success or false on fail
     */
    public function add_header( $key, $value )
    {
        if( !is_string( $key ) or !is_string( $value ) )
            return false;

        if( empty( $this->_headers ) )
            $this->_headers = array();

        $this->_headers[trim($key)] = ltrim( $value );
        return true;
    }

    /**
     * Returns current variables to be sent in GET
     *
     * @return array Returns current variables to be sent in GET
     */
    public function get_get_variables()
    {
        if( empty( $this->_get ) or !is_array( $this->_get ) )
            $this->_get = array();

        return $this->_get;
    }

    /**
     * Set a key, value pair which will be sent in GET
     *
     * @param string $key
     * @param string $value
     *
     * @return bool Returns true on success or false on fail
     */
    public function add_get_variable( $key, $value )
    {
        if( !is_string( $key ) or !is_string( $value ) )
            return false;

        if( empty( $this->_get ) )
            $this->_get = array();

        $this->_get[$key] = $value;
        return true;
    }

    /**
     * Returns current variables to be sent in POST
     *
     * @return array Returns current variables to be sent in POST
     */
    public function get_post_variables()
    {
        if( empty( $this->_post ) or !is_array( $this->_post ) )
            $this->_post = array();

        return $this->_post;
    }

    /**
     * Set a key, value pair which will be sent in POST
     *
     * @param string $key
     * @param string $value
     *
     * @return bool Returns true on success or false on fail
     */
    public function add_post_variable( $key, $value )
    {
        if( !is_string( $key ) or !is_string( $value ) )
            return false;

        if( empty( $this->_post ) )
            $this->_post = array();

        $this->_post[$key] = $value;
        return true;
    }

    public function do_curl( $params = false )
    {
        if( !@function_exists( 'curl_init' )
         or !($ch = @curl_init()) )
        {
            $this->set_error( self::ERR_REQUEST_INIT, self::s2p_t( 'Couldn\'t initialize cURL request. Please check cURL library.' ) );
            return false;
        }

        if( !is_array( $params ) )
            $params = array();

        // Default CURL params...
        if( empty( $params['userpass'] ) or !is_array( $params['userpass'] ) or !isset( $params['userpass']['user'] ) or !isset( $params['userpass']['pass'] ) )
            $params['userpass'] = false;

        if( empty( $params['timeout'] ) )
            $params['timeout'] = 30;
        else
            $params['timeout'] = intval( $params['timeout'] );
        if( empty( $params['user_agent'] ) )
            $params['user_agent'] = 'S2P_API_Request';
        if( empty( $params['extra_get_params'] ) or !is_array( $params['extra_get_params'] ) )
            $params['extra_get_params'] = array();
        // END Default CURL params...

        if( !isset( $params['raw_post_str'] ) )
            $params['raw_post_str'] = '';
        if( empty( $params['header_keys_arr'] ) or !is_array( $params['header_keys_arr'] ) )
            $params['header_keys_arr'] = array();
        if( empty( $params['post_arr'] ) or !is_array( $params['post_arr'] ) )
            $params['post_arr'] = array();
        if( empty( $params['header_arr'] ) or !is_array( $params['header_arr'] ) )
            $params['header_arr'] = array();

        // Convert old format to new format...
        if( !empty( $params['header_arr'] ) )
        {
            foreach( $params['header_arr'] as $knti => $header_txt )
            {
                $header_value_arr = explode( ':', $header_txt );
                $key = trim( $header_value_arr[0] );
                $val = '';
                if( isset( $header_value_arr[1] ) )
                    $val = ltrim( $header_value_arr[1] );

                $params['header_keys_arr'][$key] = $val;
            }

            // Reset raw headers array as we moved them to key => value pairs...
            $params['header_arr'] = array();
        }

        $params['header_keys_arr'] = array_merge( $params['header_keys_arr'], $this->get_headers() );
        $params['post_arr'] = array_merge( $params['post_arr'], $this->get_post_variables() );
        $params['extra_get_params'] = array_merge( $params['extra_get_params'], $this->get_get_variables() );

        $post_string = '';
        if( !empty( $params['post_arr'] ) and is_array( $params['post_arr'] ) )
        {
            foreach( $params['post_arr'] as $key => $val )
            {
                // workaround for '@/local/file' fields...
                if( substr( $val, 0, 1 ) == '@' )
                {
                    $post_string = $params['post_arr'];
                    break;
                }

                $post_string .= $key.'='.utf8_encode( rawurlencode( $val ) ).'&';
            }

            if( is_string( $post_string ) and $post_string != '' )
                $post_string = substr( $post_string, 0, -1 );

            if( !isset( $params['header_keys_arr']['Content-Type'] ) )
                $params['header_keys_arr']['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        if( $params['raw_post_str'] != '' )
            $post_string .= $params['raw_post_str'];

        if( ($body = $this->get_body()) )
            $post_string .= $body;

        if( $post_string != '' )
        {
            @curl_setopt( $ch, CURLOPT_POST, true );
            @curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_string );
        }

        $url = $this->get_url();

        if( count( $params['extra_get_params'] ) )
        {
            if( strstr( $url, '?' ) === false )
                $url .= '?';

            foreach( $params['extra_get_params'] as $key => $val )
            {
                $url .= '&'.$key.'='.rawurlencode( $val );
            }
        }

        if( !empty( $params['header_keys_arr'] ) and is_array( $params['header_keys_arr'] ) )
        {
            foreach( $params['header_keys_arr'] as $key => $val )
                $params['header_arr'][] = $key.': '.$val;
        }

        if( !empty( $params['header_arr'] ) and is_array( $params['header_arr'] ) )
            @curl_setopt( $ch, CURLOPT_HTTPHEADER, $params['header_arr'] );

        if( !empty( $params['user_agent'] ) )
            curl_setopt( $ch, CURLOPT_USERAGENT, $params['user_agent'] );

        @curl_setopt( $ch, CURLOPT_URL, $url );
        @curl_setopt( $ch, CURLOPT_HEADER, 0 );
        if( defined( 'CURLINFO_HEADER_OUT' ) )
            @curl_setopt( $ch, constant( 'CURLINFO_HEADER_OUT' ), true );
        @curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        @curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        @curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        @curl_setopt( $ch, CURLOPT_TIMEOUT, $params['timeout'] );

        if( !empty( $params['userpass'] ) )
            @curl_setopt( $ch, CURLOPT_USERPWD, $params['userpass']['user'].':'.$params['userpass']['pass'] );

        $response_buf = @curl_exec( $ch );

        $this->_response_buffer = $response_buf;

        $return_params = $params;
        if( isset( $return_params['userpass']['pass'] ) )
            $return_params['userpass']['pass'] = '(undisclosed_pass)';

        if( !($curl_info = @curl_getinfo( $ch )) )
            $curl_info = false;

        $response = array(
            'response' => $response_buf,
            'http_code' => ((!empty( $curl_info ) and !empty( $curl_info['http_code'] ))?$curl_info['http_code']:0),
            'request_details' => $curl_info,
            'request_error_msg' => @curl_error( $ch ),
            'request_error_no' => @curl_errno( $ch ),
            'request_params' => $return_params,
        );

        @curl_close( $ch );

        return $response;
    }

}