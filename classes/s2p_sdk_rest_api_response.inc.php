<?php

namespace S2P_SDK;

class S2P_SDK_Rest_API_Response extends S2P_SDK_Language
{
    private $_request_body = '';
    private $_request_get = null;
    private $_request_headers = null;

    function __construct()
    {
        parent::__construct();
        $this->reset_api_request();
    }

    private function reset_api_request()
    {
        $this->_request_body = '';
        $this->_request_get = null;
        $this->_request_headers = null;
    }

    public function get_headers()
    {
        return $this->_request_headers;
    }

    public function get_get_variables()
    {
        return $this->_request_get;
    }

    public function get_body()
    {
        return $this->_request_body;
    }

    public function add_header( $key, $value )
    {
        if( empty( $this->_request_headers ) )
            $this->_request_headers = array();

        $this->_request_headers[$key] = $value;
    }

    public function add_get_variable( $key, $value )
    {
        if( empty( $this->_request_get ) )
            $this->_request_get = array();

        $this->_request_get[$key] = $value;
    }

    public function set_body( $body )
    {
        $this->_request_body = $body;
    }
}
