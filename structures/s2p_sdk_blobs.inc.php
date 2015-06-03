<?php
namespace S2P_SDK;

class S2P_SDK_Blobs extends S2P_SDK_Module
{
    const ERR_TYPE = 1, ERR_VALIDITY = 2;

    /** @var int $_type */
    protected $_type;
    /** @var string $_raw_value */
    protected $_raw_value;
    /** @var mixed $_value */
    protected $_value;

    // /payments
    const MerchantTransactionID = 'merchanttransactionid', Amount = 'amount', Currency = 'currency', ReturnURL = 'returnurl',

          // PAYMENT - GLOBALPAY CONTROLLED PARAMETERS
          ID = 'id', RedirectURL = 'redirecturl', Status = 'status', Reasons = 'reasons', Code = 'code', Info = 'info', MethodTransactionID = 'methodtransactionid',

          // PAYMENT - OPTIONAL PARAMETERS
          MethodID = 'methodid', MethodOptionID = 'methodoptionid', SiteID = 'siteid', Description = 'description';

    const TYPE_STRING = 1, TYPE_INT = 2, TYPE_FLOAT = 3, TYPE_ARRAY = 4, TYPE_STATUS_BLOB = 5, TYPE_REASONS_BLOB = 6;

    private static $BLOBS_ARR = array(
        self::MerchantTransactionID => array(
            'external_name' => 'MerchantTransactionID',
            'regexp' => '^[0-9a-zA-Z_-]{1,50}$',
            'type' => self::TYPE_STRING,
        ),
        self::Amount => array(
            'external_name' => 'Amount',
            'regexp' => '^\d{1,12}$',
            'type' => self::TYPE_INT,
        ),
        self::Currency => array(
            'external_name' => 'Currency',
            'regexp' => '^[A-Z]{3}$',
            'type' => self::TYPE_STRING,
        ),
        self::ReturnURL => array(
            'external_name' => 'ReturnURL',
            'regexp' => '^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$',
            'type' => self::TYPE_STRING,
        ),
        self::ID => array(
            'external_name' => 'ID',
            'regexp' => '^\d{1,12}$',
            'type' => self::TYPE_INT,
        ),
        self::RedirectURL => array(
            'external_name' => 'RedirectURL',
            'regexp' => '^(https:\/\/[apitest|api]\.smart2pay\.com\/Home\?PaymentToken=[A‐Z0‐9]{32}\.\d{1,12})$',
            'type' => self::TYPE_STRING,
        ),
        self::Status => array(
            'external_name' => 'Status',
            'type' => self::TYPE_STATUS_BLOB,
        ),
        self::Reasons => array(
            'external_name' => 'Reasons',
            'type' => self::TYPE_ARRAY,
            'array_type' => self::TYPE_REASONS_BLOB,
        ),
        self::Code => array(
            'external_name' => 'Code',
            'type' => self::TYPE_INT,
        ),
        self::Info => array(
            'external_name' => 'Info',
            'type' => self::TYPE_STRING,
        ),
        self::MethodTransactionID => array(
            'external_name' => 'MethodTransactionID',
            'regexp' => '^[0-9a-zA-Z_‐]{1,50}$',
            'type' => self::TYPE_STRING,
        ),
        self::MethodID => array(
            'external_name' => 'MethodID',
            'regexp' => '^([0-9]{1,10})$',
            'type' => self::TYPE_INT,
        ),
        self::MethodOptionID => array(
            'external_name' => 'MethodOptionID',
            'regexp' => '^([0-9]{1,10})$',
            'type' => self::TYPE_INT,
        ),
        self::SiteID => array(
            'external_name' => 'SiteID',
            'regexp' => '^([0-9]{1,10})$',
            'type' => self::TYPE_INT,
        ),
        self::Description => array(
            'external_name' => 'Description',
            'regexp' => '^.{1,255}$',
            'type' => self::TYPE_STRING,
        ),
    );

    /**
     * This method is called right after module instance is created
     *
     * @param bool|array $module_params
     *
     * @return mixed
     */
    public function init( $module_params = false )
    {
        // Let classes know about this hook...
        $this->register_hook( 'blob_extract_value' );

        if( !empty( $module_params ) and is_array( $module_params )
        and isset( $module_params['blob_type'] ) and isset( $module_params['value'] ) )
        {
            $this->validate( $module_params['blob_type'], $module_params['value'] );
        }
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
        $this->reset_blob();
    }

    public function validate( $blob_type, $value )
    {
        $this->reset_blob();

        $blob_type = trim( $blob_type );
        if( !($blob_details = self::valid_blob( $blob_type )) )
        {
            $this->set_error( self::ERR_TYPE,
                $this->s2p_t( 'Invalid blob type.' ),
                $this->s2p_t( 'Invalid blob type [%s] for value [%s]', $blob_type, self::mixed_to_string( $value ) ) );
            return false;
        }

        if( !self::is_valid( $blob_type, $value ) )
        {
            $this->set_error( self::ERR_VALIDITY,
                $this->s2p_t( '[%s] is not valid.', $blob_details['external_name'] ),
                $this->s2p_t( '[%s] doesn\'t pass regexp [%s] for value [%s].', $blob_details['external_name'], $blob_details['regexp'], self::mixed_to_string( $value ) ) );
            return false;
        }

        $this->_type = $blob_type;
        $this->_raw_value = $value;
        $this->_value = $this->extract_value( $blob_details, $value );

        return true;
    }

    public function extract_value( $blob_type, $value )
    {
        $blob_type = trim( $blob_type );
        if( !($blob_details = self::valid_blob( $blob_type )) )
            return null;

        $result = null;
        switch( $blob_details['type'] )
        {
            case self::TYPE_STRING:
                if( is_string( $value ) )
                    $result = $value;
            break;
            case self::TYPE_INT:
                if( is_scalar( $value ) )
                    $result = intval( $value );
            break;
            case self::TYPE_FLOAT:
                if( is_scalar( $value ) )
                    $result = floatval( $value );
            break;
            case self::TYPE_ARRAY:
                if( is_array( $value ) )
                    $result = $value;
            break;
            case self::TYPE_STATUS_BLOB:
            break;
            case self::TYPE_REASONS_BLOB:
            break;
        }

        $hook_args = array();
        $hook_args['blob_type'] = $blob_type;
        $hook_args['blob_details'] = $blob_details;
        $hook_args['result'] = $result;

        // Let external classes change value validation
        if( ($hook_result = $this->trigger_hooks( 'blob_extract_value', $hook_args )) !== null
        and isset( $hook_result['result'] ) )
            $result = $hook_result['result'];

        return $result;
    }

    public function reset_blob()
    {
        $this->_type = '';
        $this->_raw_value = null;
        $this->_value = null;
    }

    public function type()
    {
        return $this->_type;
    }

    public function raw_value()
    {
        return $this->_raw_value;
    }

    public function value()
    {
        return $this->_value;
    }

    public static function is_valid( $blob_type, $value )
    {
        $blob_type = trim( $blob_type );
        if( !($blob_details = self::valid_blob( $blob_type ))
         or (!empty( $blob_details['regexp'] )
                and !preg_match( '/'.preg_quote( $blob_details['regexp'], '/' ).'/', $value )) )
            return false;

        return true;
    }

    public static function get_blobs()
    {
        static $validators_are_valid = false;

        if( !empty( $validators_are_valid ) )
            return self::$BLOBS_ARR;

        foreach( self::$BLOBS_ARR as $blob => $validator_arr )
        {
            self::$BLOBS_ARR[$blob] = self::_validate_blob_validator( $validator_arr );
        }

        $validators_are_valid = true;

        return self::$BLOBS_ARR;
    }

    public static function valid_blob( $blob_type )
    {
        $blob_type = trim( $blob_type );
        if( empty( $blob_type )
         or !($blobs_arr = self::get_blobs()) or empty( $blobs_arr[$blob_type] ) )
            return false;

        return $blobs_arr[$blob_type];
    }

    private static function _default_validator_fields()
    {
        return array(
            'external_name' => '',
            'regexp' => '',
            'type' => self::TYPE_STRING,
            // in case type is TYPE_ARRAY this says type of each element in array
            'array_type' => 0,
            'group' => self::TYPE_STRING,
        );
    }

    private static function _validate_blob_validator( $validator_arr )
    {
        $def_values = self::_default_validator_fields();
        if( empty( $validator_arr ) or !is_array( $validator_arr ) )
            return $def_values;

        foreach( $def_values as $key => $def_value )
        {
            if( !array_key_exists( $key, $validator_arr ) )
                $validator_arr[$key] = $def_value;
        }

        return $validator_arr;
    }

}