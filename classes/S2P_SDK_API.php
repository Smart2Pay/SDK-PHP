<?php
namespace S2P_SDK;

class S2P_SDK_API extends S2P_SDK_Module
{
    public const TYPE_REST = 'rest';

    public const ERR_API_TYPE = 1, ERR_API_OBJECT = 2, ERR_API_CALL = 3, ERR_INPUT = 4;

    /** @var string */
    private $_api_type = self::TYPE_REST;

    /** @var S2P_SDK_Rest_API */
    private $_api;

    /** @var array */
    private $_finalize_result;

    /** @var float */
    private $_call_time = 0;

    public function __construct($params = false)
    {
        parent::__construct($params);
    }

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed If this function returns false it will consider module is not initialized correctly and will not return class instance
     */
    public function init($module_params = false)
    {
        $this->reset_api();

        if (empty($module_params) || !is_array($module_params)) {
            $module_params = [];
        }

        if (empty($module_params['api_type']) || !self::valid_api_type($module_params['api_type'])) {
            $module_params['api_type'] = self::TYPE_REST;
        }

        if (!$this->api_type($module_params['api_type'])) {
            return false;
        }

        $api_config_arr = self::get_api_configuration();

        //
        // Small hack which checks if we have a SmartCards method and method to be used by SDK is 'payments',
        // switch SDK method to 'cards' so we are correctly redirected to SmartCards API entry point
        //
        if (!empty($module_params['method'])
        && $module_params['method'] === 'payments'
        && !empty($module_params['method_params']) && is_array($module_params['method_params'])
        && !empty($module_params['method_params']['payment']) && is_array($module_params['method_params']['payment'])
        && !empty($module_params['method_params']['payment']['methodid'])
        && self::is_smartcards_method($module_params['method_params']['payment']['methodid'])) {
            $module_params['method'] = 'cards';
        }

        if (empty($module_params['site_id']) && !empty($api_config_arr['site_id'])) {
            $module_params['site_id'] = $api_config_arr['site_id'];
        }
        if (empty($module_params['api_key']) && !empty($api_config_arr['api_key'])) {
            $module_params['api_key'] = $api_config_arr['api_key'];
        }
        if (empty($module_params['environment']) && !empty($api_config_arr['environment'])) {
            $module_params['environment'] = $api_config_arr['environment'];
        }
        if (empty($module_params['custom_base_url']) && !empty($api_config_arr['custom_base_url'])) {
            $module_params['custom_base_url'] = $api_config_arr['custom_base_url'];
        }

        return !(((!empty($module_params['api_key']) && !empty($module_params['site_id'])) || !empty($module_params['method']))
        && !$this->create_api_object($module_params));
    }

    /**
     * This method is called when destroy_instances() method is called inside any module.
     * This is ment to be destructor of instances.
     * Make sure you call destroy_instances() when you don't need any data held in any instances of modules
     *
     * @see destroy_instances()
     */
    public function destroy() : void
    {
        $this->reset_api();
    }

    public function api_type($type = null)
    {
        if ($type === null) {
            return $this->_api_type;
        }

        if (!in_array($type, [self::TYPE_REST])) {
            $this->set_error(self::ERR_API_TYPE,
                self::s2p_t('Unknown API type'),
                sprintf('Unknown API type [%s]', $type));

            return false;
        }

        $this->_api_type = $type;

        return true;
    }

    /**
     * Return current API object
     *
     * @return S2P_SDK_Rest_API Returns API object
     */
    public function get_api_obj()
    {
        return $this->_api;
    }

    /**
     * Returns how many microseconds were spent on API call
     *
     * @return int Returns API call time
     */
    public function get_call_time()
    {
        return $this->_call_time;
    }

    /**
     * Return JSON decoded array from server response
     *
     * @return null|array Return JSON decoded array from server response
     */
    public function get_result() : ?array
    {
        if (empty($this->_api)) {
            return null;
        }

        $call_result = $this->_api->get_call_result();

        if (!isset($call_result['response']['response_array'])
            || !is_array($call_result['response']['response_array'])) {
            return null;
        }

        return $call_result['response']['response_array'];
    }

    public function do_call($params = false)
    {
        if (!$this->create_api_object($params)) {
            $this->set_error(self::ERR_API_OBJECT, self::s2p_t('Couldn\'t initiate API object.'));

            return false;
        }

        $this->reset_api(false);

        $this->_call_time = 0;
        $call_start = microtime(true);
        if (!($call_result = $this->_api->do_call($params))) {
            self::reset_one_call_settings();

            $this->_call_time = microtime(true) - $call_start;
            if ($this->_api->has_error()) {
                $this->copy_error($this->_api);
            } else {
                $this->set_error(self::ERR_API_CALL, self::s2p_t('Error in API call.'));
            }

            return false;
        }

        self::reset_one_call_settings();

        $this->_call_time = microtime(true) - $call_start;

        return $call_result;
    }

    public function do_finalize(array $params = [])
    {
        if (empty($this->_api)) {
            $this->set_error(self::ERR_API_OBJECT, self::s2p_t('Couldn\'t finalize, API object is empty.'));

            return false;
        }

        // If redirect is required, send redirect headers now...
        if (!isset($params['redirect_now'])) {
            $params['redirect_now'] = true;
        }

        if (!($finalize_result = $this->_api->do_finalize($params))) {
            if ($this->_api->has_error()) {
                $this->copy_error($this->_api);
            } else {
                $this->set_error(self::ERR_API_CALL, self::s2p_t('Couldn\'t finialize API action.'));
            }

            return false;
        }

        if (!empty($params['redirect_now'])
            && !empty($finalize_result['should_redirect'])
            && !empty($finalize_result['redirect_to'])
            && empty($finalize_result['redirect_headers_set'])
            && !@headers_sent()) {
            @header('Location: '.$finalize_result['redirect_to']);

            $finalize_result['redirect_headers_set'] = true;
        }

        $this->_finalize_result = $finalize_result;

        return $this->_finalize_result;
    }

    private function reset_api(bool $full_reset = true) : void
    {
        if ($full_reset) {
            $this->_api = null;
            $this->_api_type = self::TYPE_REST;
        }

        $this->_call_time = 0;
    }

    private function create_api_object($api_params = false)
    {
        $type = $this->api_type();

        switch ($type) {
            default:
                $this->set_error(self::ERR_API_TYPE,
                    self::s2p_t('Unknown API type'),
                    sprintf('Unknown API type [%s]', $type));

                return false;
            case self::TYPE_REST:
                if (empty($this->_api)
                && !($this->_api = self::get_instance('S2P_SDK_Rest_API', $api_params, false))) {
                    if (self::st_has_error()) {
                        $this->copy_static_error();
                    } else {
                        $this->set_error(self::ERR_API_OBJECT, self::s2p_t('Couldn\'t initiate API object.'));
                    }

                    return false;
                }
                break;
        }

        return true;
    }

    public static function valid_api_type($type)
    {
        return !(!in_array($type, [self::TYPE_REST]));
    }
}
