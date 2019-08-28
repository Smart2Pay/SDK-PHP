<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Device_Info extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'deviceinfo',
            'external_name' => 'DeviceInfo',
            'type' => S2P_SDK_VTYPE_BLOB,
            'structure' => $this->get_structure_definition(),
        );
    }

    /**
     * @inheritDoc
     */
    public function get_structure_definition()
    {
        return array(
            array(
                'name' => 'browseracceptheader',
                'external_name' => 'BrowserAcceptHeader',
                'display_name' => self::s2p_t( 'Browser accept header' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'browseruseragent',
                'external_name' => 'BrowserUserAgent',
                'display_name' => self::s2p_t( 'Browser user agent' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'browserjavaenabled',
                'external_name' => 'BrowserJavaEnabled',
                'display_name' => self::s2p_t( 'Broser Java enabled' ),
                'type' => S2P_SDK_VTYPE_BOOL,
                'default' => true,
            ),
            array(
                'name' => 'browserjavascriptenabled',
                'external_name' => 'BrowserJavaScriptEnabled',
                'display_name' => self::s2p_t( 'Browser JavaScript enabled' ),
                'type' => S2P_SDK_VTYPE_BOOL,
                'default' => true,
            ),
            array(
                'name' => 'browserlanguage',
                'external_name' => 'BrowserLanguage',
                'display_name' => self::s2p_t( 'Browser language' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
            ),
            array(
                'name' => 'browsercolordepth',
                'external_name' => 'BrowserColorDepth',
                'display_name' => self::s2p_t( 'Browser color depth' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'skip_if_default' => true,
            ),
            array(
                'name' => 'browserscreenheight',
                'external_name' => 'BrowserScreenHeight',
                'display_name' => self::s2p_t( 'Browser screen height' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'skip_if_default' => true,
            ),
            array(
                'name' => 'browserscreenwidth',
                'external_name' => 'BrowserScreenWidth',
                'display_name' => self::s2p_t( 'Browser screen width' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'skip_if_default' => true,
            ),
            array(
                'name' => 'browsertimezone',
                'external_name' => 'BrowserTimeZone',
                'display_name' => self::s2p_t( 'Browser timezone' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'skip_if_default' => true,
            ),
        );
    }

}
