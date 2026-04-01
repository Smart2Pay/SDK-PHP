<?php
namespace S2P_SDK;

class S2P_SDK_Rest_API_Request extends S2P_SDK_Language
{
    public const ERR_REQUEST_INIT = 1;

    /** @var string */
    private $_url = '';

    /** @var string */
    private $_body = '';

    /** @var string */
    private $_http_method = 'GET';

    /** @var array */
    private $_get = [];

    /** @var array */
    private $_post = [];

    /** @var array */
    private $_headers = [];

    /**
     * Response details about last API call
     * @var array */
    private $_request_result;

    public function __construct()
    {
        parent::__construct();
        $this->reset_api_request();
    }

    public function get_headers()
    {
        if (empty($this->_headers) || !is_array($this->_headers)) {
            $this->_headers = [];
        }

        return $this->_headers;
    }

    /**
     * Return details about last API call
     *
     * @return null|array Returns details about last API call
     */
    public function get_request_result()
    {
        return $this->_request_result;
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
    public function set_url($url)
    {
        if (!is_string($url)) {
            return false;
        }

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
    public function set_body($body)
    {
        if (!is_string($body)) {
            return false;
        }

        $this->_body = $body;

        return true;
    }

    /**
     * Get HTTP method to be used
     *
     * @return string Returns HTTP method to be used
     */
    public function get_http_method()
    {
        return $this->_http_method;
    }

    /**
     * Set HTTP method
     *
     * @param string $meth
     *
     * @return bool Returns true on success or false on fail
     */
    public function set_http_method(string $meth) : bool
    {
        if (!self::valid_http_method($meth)) {
            return false;
        }

        $this->_http_method = $meth;

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
    public function add_header($key, $value)
    {
        if (!is_string($key) || !is_string($value)) {
            return false;
        }

        if (empty($this->_headers)) {
            $this->_headers = [];
        }

        $this->_headers[trim($key)] = ltrim($value);

        return true;
    }

    /**
     * Returns current variables to be sent in GET
     *
     * @return array Returns current variables to be sent in GET
     */
    public function get_get_variables()
    {
        if (empty($this->_get) || !is_array($this->_get)) {
            $this->_get = [];
        }

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
    public function add_get_variable($key, $value)
    {
        if (!is_string($key) || !is_string($value)) {
            return false;
        }

        if (empty($this->_get)) {
            $this->_get = [];
        }

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
        if (empty($this->_post) || !is_array($this->_post)) {
            $this->_post = [];
        }

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
    public function add_post_variable($key, $value)
    {
        if (!is_string($key) || !is_string($value)) {
            return false;
        }

        if (empty($this->_post)) {
            $this->_post = [];
        }

        $this->_post[$key] = $value;

        return true;
    }

    /**
     * @param bool|array $params
     *
     * @return bool|array
     */
    public function do_curl($params = false)
    {
        if (!@function_exists('curl_init')
         || !($ch = @curl_init())) {
            $this->set_error(self::ERR_REQUEST_INIT, self::s2p_t('Couldn\'t initialize cURL request. Please check cURL library.'));

            return false;
        }

        if (!is_array($params)) {
            $params = [];
        }

        // Default CURL params...
        if (empty($params['userpass']) || !is_array($params['userpass']) || !isset($params['userpass']['user']) || !isset($params['userpass']['pass'])) {
            $params['userpass'] = false;
        }

        if (empty($params['timeout'])) {
            $params['timeout'] = 30;
        } else {
            $params['timeout'] = intval($params['timeout']);
        }
        if (empty($params['user_agent'])) {
            $params['user_agent'] = 'S2P_API_Request';
        }
        if (empty($params['extra_get_params']) || !is_array($params['extra_get_params'])) {
            $params['extra_get_params'] = [];
        }
        // END Default CURL params...

        if (!isset($params['raw_post_str'])) {
            $params['raw_post_str'] = '';
        }
        if (empty($params['header_keys_arr']) || !is_array($params['header_keys_arr'])) {
            $params['header_keys_arr'] = [];
        }
        if (empty($params['post_arr']) || !is_array($params['post_arr'])) {
            $params['post_arr'] = [];
        }
        if (empty($params['header_arr']) || !is_array($params['header_arr'])) {
            $params['header_arr'] = [];
        }
        if (empty($params['curl_init_callback']) || !is_callable($params['curl_init_callback'])) {
            $params['curl_init_callback'] = false;
        }
        if (empty($params['proxy_server'])) {
            $params['proxy_server'] = '';
        }
        if (empty($params['proxy_auth'])) {
            $params['proxy_auth'] = '';
        }
        if (empty($params['connection_ssl_version'])) {
            if (!defined('CURL_SSLVERSION_TLSv1_2')) {
                $params['connection_ssl_version'] = 6;
            } else {
                $params['connection_ssl_version'] = CURL_SSLVERSION_TLSv1_2;
            }
        }

        if (!empty($params['curl_init_callback'])) {
            if (($curl_init_result = @call_user_func($params['curl_init_callback'], ['ch' => $ch, 'params' => $params]))
            && is_array($curl_init_result)) {
                if (!empty($curl_init_result['params']) && is_array($curl_init_result['params'])) {
                    $params = $curl_init_result['params'];
                }
            }
        }

        // Convert old format to new format...
        if (!empty($params['header_arr'])) {
            foreach ($params['header_arr'] as $knti => $header_txt) {
                $header_value_arr = explode(':', $header_txt);
                $key = trim($header_value_arr[0]);
                $val = '';
                if (isset($header_value_arr[1])) {
                    $val = ltrim($header_value_arr[1]);
                }

                $params['header_keys_arr'][$key] = $val;
            }

            // Reset raw headers array as we moved them to key => value pairs...
            $params['header_arr'] = [];
        }

        $params['header_keys_arr'] = array_merge($params['header_keys_arr'], $this->get_headers());
        $params['post_arr'] = array_merge($params['post_arr'], $this->get_post_variables());
        $params['extra_get_params'] = array_merge($params['extra_get_params'], $this->get_get_variables());

        $post_string = '';
        if (!empty($params['post_arr']) && is_array($params['post_arr'])) {
            foreach ($params['post_arr'] as $key => $val) {
                // workaround for '@/local/file' fields...
                if (substr($val, 0, 1) == '@') {
                    $post_string = $params['post_arr'];
                    break;
                }

                $post_string .= $key.'='.utf8_encode(rawurlencode($val)).'&';
            }

            if (is_string($post_string) && $post_string != '') {
                $post_string = substr($post_string, 0, -1);
            }

            if (!isset($params['header_keys_arr']['Content-Type'])) {
                $params['header_keys_arr']['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        if ($params['raw_post_str'] != '') {
            $post_string .= $params['raw_post_str'];
        }

        if (($body = $this->get_body())) {
            $post_string .= $body;
        }

        $http_method = $this->get_http_method();

        if ($post_string != '') {
            if (in_array($http_method, ['POST', 'GET'])) {
                @curl_setopt($ch, CURLOPT_POST, true);
            }
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        }

        $params['header_keys_arr']['Content-Length'] = strlen($post_string);

        @curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);

        $url = $this->get_url();

        if (count($params['extra_get_params'])) {
            if (strstr($url, '?') === false) {
                $url .= '?';
            }

            foreach ($params['extra_get_params'] as $key => $val) {
                $url .= '&'.$key.'='.rawurlencode($val);
            }
        }

        if (!empty($params['header_keys_arr']) && is_array($params['header_keys_arr'])) {
            foreach ($params['header_keys_arr'] as $key => $val) {
                $params['header_arr'][] = $key.': '.$val;
            }
        }

        if (!empty($params['header_arr']) && is_array($params['header_arr'])) {
            @curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header_arr']);
        }

        if (!empty($params['user_agent'])) {
            @curl_setopt($ch, CURLOPT_USERAGENT, $params['user_agent']);
        }

        if (!empty($params['proxy_server'])) {
            @curl_setopt($ch, CURLOPT_PROXY, $params['proxy_server']);
        }
        if (!empty($params['proxy_auth'])) {
            @curl_setopt($ch, CURLOPT_PROXYUSERPWD, $params['proxy_auth']);
        }

        @curl_setopt($ch, CURLOPT_URL, $url);
        @curl_setopt($ch, CURLOPT_HEADER, 1);
        if (defined('CURLINFO_HEADER_OUT')) {
            @curl_setopt($ch, constant('CURLINFO_HEADER_OUT'), true);
        }
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        @curl_setopt($ch, CURLOPT_SSLVERSION, $params['connection_ssl_version']);

        @curl_setopt($ch, CURLOPT_TIMEOUT, $params['timeout']);

        if (!empty($params['userpass'])) {
            @curl_setopt($ch, CURLOPT_USERPWD, $params['userpass']['user'].':'.$params['userpass']['pass']);
        }

        $response_buf = @curl_exec($ch);

        $request_headers = [];
        if (($header_lines_arr = explode("\n", $response_buf))
        && is_array($header_lines_arr)) {
            $new_response_buf = '';
            $in_body = false;
            $in_headers = true;
            $headers_received = false;
            foreach ($header_lines_arr as $line_no => $line) {
                if ($in_headers && rtrim($line) === '') {
                    $headers_received = true;
                    $in_headers = false;
                    continue;
                }

                if ($headers_received) {
                    if (strtolower(substr($line, 0, 7)) != 'http/1.') {
                        $in_body = true;
                    } else {
                        $in_headers = true;
                        $headers_received = false;
                    }
                }

                if ($in_body || !$in_headers) {
                    $new_response_buf .= $line."\n";
                } else {
                    if (($header_key_val = explode(':', $line, 2))
                    && is_array($header_key_val)) {
                        $key = false;
                        if (!isset($header_key_val[1])) {
                            $val = $header_key_val[0];
                        } else {
                            $key = trim($header_key_val[0]);
                            $val = ltrim($header_key_val[1]);
                        }

                        $val = trim($val);

                        if ($key === false) {
                            $request_headers[] = $val;
                        } else {
                            $request_headers[$key] = $val;
                        }
                    }
                }
            }

            $response_buf = $new_response_buf;
            unset($new_response_buf);
        }

        $return_params = $params;
        if (isset($return_params['userpass']['pass'])) {
            $return_params['userpass']['pass'] = '(undisclosed_pass)';
        }

        if (!($curl_info = @curl_getinfo($ch))) {
            $curl_info = false;
        }

        $response = self::default_response_array();

        $response['request_buffer'] = $post_string;
        if (!empty($curl_info) && !empty($curl_info['http_code'])) {
            $response['http_code'] = $curl_info['http_code'];
        }
        $response['request_details'] = $curl_info;
        $response['request_error_msg'] = @curl_error($ch);
        $response['request_error_no'] = @curl_errno($ch);
        $response['request_params'] = $return_params;

        $response['response_headers'] = $request_headers;
        $response['response_buffer'] = $response_buf;

        $this->_request_result = $response;

        @curl_close($ch);

        return $response;
    }

    private function reset_api_request() : void
    {
        $this->_url = '';
        $this->_body = '';
        $this->_http_method = 'GET';
        $this->_get = [];
        $this->_post = [];
        $this->_headers = [];
        $this->_request_result = null;
    }

    public static function get_http_methods() : array
    {
        return ['GET', 'POST', 'DELETE', 'PATCH'];
    }

    public static function valid_http_method(string $method) : bool
    {
        return in_array($method, self::get_http_methods(), true);
    }

    public static function default_response_array() : array
    {
        return [
            'http_code'         => 0,
            'request_details'   => [],
            'request_error_msg' => '',
            'request_error_no'  => 0,
            'request_params'    => [],

            'response_headers' => '',

            'request_buffer'  => '',
            'response_buffer' => '',
        ];
    }

    public static function validate_request_array(array $arr) : array
    {
        $default_arr = self::default_response_array();
        foreach ($default_arr as $key => $def_val) {
            if (!array_key_exists($key, $arr)) {
                $arr[$key] = $def_val;
            }
        }

        return $arr;
    }
}
