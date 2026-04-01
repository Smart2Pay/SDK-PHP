<?php
namespace S2P_SDK;

class S2P_SDK_Values_Source_Methods extends S2P_SDK_Language
{
    private static $ALL_METHODS_ARR;

    private static $AVAILABLE_METHODS_ARR;

    public static function default_method_details()
    {
        return [
            'id'          => 0,
            'displayname' => '',
            'description' => '',
            'logourl'     => '',
            'active'      => false,
        ];
    }

    public static function validate_method_details($method_arr)
    {
        $default_values = self::default_method_details();

        if (empty($method_arr) && !is_array($method_arr)) {
            return $default_values;
        }

        $new_method_arr = [];
        foreach ($default_values as $key => $val) {
            if (!array_key_exists($key, $method_arr)) {
                $new_method_arr[$key] = $val;
            } else {
                $new_method_arr[$key] = $method_arr[$key];
            }
        }

        return $new_method_arr;
    }

    public static function get_all_methods($methods_params = false)
    {
        if (self::$ALL_METHODS_ARR !== null) {
            return self::$ALL_METHODS_ARR;
        }

        if (empty($methods_params) || !is_array($methods_params)) {
            $methods_params = [];
        }

        $api_params = $methods_params;
        $api_params['method'] = 'methods';
        $api_params['func'] = 'list_all';

        self::$ALL_METHODS_ARR = self::do_methods_call($api_params);

        return self::$ALL_METHODS_ARR;
    }

    public static function get_available_methods($methods_params = false)
    {
        if (self::$AVAILABLE_METHODS_ARR !== null) {
            return self::$AVAILABLE_METHODS_ARR;
        }

        if (empty($methods_params) || !is_array($methods_params)) {
            $methods_params = [];
        }

        $api_params = $methods_params;
        $api_params['method'] = 'methods';
        $api_params['func'] = 'assigned_methods';

        self::$AVAILABLE_METHODS_ARR = self::do_methods_call($api_params);

        return self::$AVAILABLE_METHODS_ARR;
    }

    /**
     * @param int $method_id Method ID
     * @param bool|array $methods_params Extra parameters sent to API call (if needed)
     *
     * @return array|bool
     */
    public static function get_method_details($method_id, $methods_params = false)
    {
        $method_id = (int)$method_id;
        if (empty($method_id)) {
            return false;
        }

        if (empty($methods_params) || !is_array($methods_params)) {
            $methods_params = [];
        }

        $api_params = $methods_params;
        $api_params['method'] = 'methods';
        $api_params['func'] = 'method_details';
        $api_params['get_variables'] = ['id' => $method_id];

        /** @var S2P_SDK_API $api */
        if (!($api = S2P_SDK_Module::get_instance('S2P_SDK_API', $api_params, false))
         || !$api->do_call(['allow_remote_calls' => true])
         || !($call_result = $api->get_result())
         || !is_array($call_result)
         || !($finalize_arr = $api->do_finalize(['redirect_now' => false]))
         || empty($call_result['method']) || !is_array($call_result['method'])
         || empty($finalize_arr) || !is_array($finalize_arr)) {
            return false;
        }

        $return_arr = [];
        $return_arr['method_arr'] = $call_result['method'];
        $return_arr['custom_validators'] = $finalize_arr['custom_validators'];

        return $return_arr;
    }

    public static function valid_method_id($method_id, $methods_params = false)
    {
        if (empty($methods_params) || !is_array($methods_params)) {
            $methods_params = [];
        }

        $method_id = (int)$method_id;
        if (empty($method_id)
         || !($methods_arr = self::get_all_methods($methods_params)) || empty($methods_arr[$method_id])) {
            return false;
        }

        return $methods_arr[$method_id];
    }

    public static function valid_available_method_id($method_id, $methods_params = false)
    {
        if (empty($methods_params) || !is_array($methods_params)) {
            $methods_params = [];
        }

        $method_id = (int)$method_id;
        if (empty($method_id)
         || !($methods_arr = self::get_available_methods($methods_params)) || empty($methods_arr[$method_id])) {
            return false;
        }

        return $methods_arr[$method_id];
    }

    public static function guess_all_from_term($term, $methods_params = false)
    {
        if (empty($methods_params) || !is_array($methods_params)) {
            $methods_params = [];
        }

        if (!($all_terms_arr = self::get_all_methods($methods_params))) {
            return [];
        }

        $found_terms = [];
        foreach ($all_terms_arr as $key => $val) {
            if (stristr($term, $val['displayname']) === false) {
                continue;
            }

            $found_terms[$key] = $val['displayname'];
        }

        return $found_terms;
    }

    public static function guess_available_from_term($term, $methods_params = false)
    {
        if (empty($methods_params) || !is_array($methods_params)) {
            $methods_params = [];
        }

        if (!($all_terms_arr = self::get_available_methods($methods_params))) {
            return [];
        }

        $found_terms = [];
        foreach ($all_terms_arr as $key => $val) {
            if (stristr($term, $val['displayname']) === false) {
                continue;
            }

            $found_terms[$key] = $val['displayname'];
        }

        return $found_terms;
    }

    private static function do_methods_call($api_params)
    {
        /** @var S2P_SDK_API $api */
        if (!($api = S2P_SDK_Module::get_instance('S2P_SDK_API', $api_params, false))
         || !$api->do_call(['allow_remote_calls' => true])
         || !($call_result = $api->get_result())
         || !is_array($call_result)
         || empty($call_result['methods']) || !is_array($call_result['methods'])) {
            return [];
        }

        $methods_arr = [];
        foreach ($call_result['methods'] as $method_arr) {
            if (!($method_details_arr = self::validate_method_details($method_arr))
             || empty($method_details_arr['id'])) {
                continue;
            }

            $methods_arr[$method_details_arr['id']] = $method_details_arr;
        }

        return $methods_arr;
    }
}
