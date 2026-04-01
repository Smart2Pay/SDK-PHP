<?php
namespace S2P_SDK;

class S2P_SDK_Structure_Device_Info extends S2P_SDK_Scope_Structure
{
    /**
     * @inheritdoc
     */
    public function get_definition() : array
    {
        return [
            'name'          => 'deviceinfo',
            'external_name' => 'DeviceInfo',
            'type'          => S2P_SDK_VTYPE_BLOB,
            'structure'     => $this->get_structure_definition(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_structure_definition() : array
    {
        return [
            [
                'name'          => 'browseracceptheader',
                'external_name' => 'BrowserAcceptHeader',
                'display_name'  => self::s2p_t('Browser accept header'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'browseruseragent',
                'external_name' => 'BrowserUserAgent',
                'display_name'  => self::s2p_t('Browser user agent'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'          => 'browserjavaenabled',
                'external_name' => 'BrowserJavaEnabled',
                'display_name'  => self::s2p_t('Broser Java enabled'),
                'type'          => S2P_SDK_VTYPE_BOOL,
                'default'       => true,
            ],
            [
                'name'          => 'browserjavascriptenabled',
                'external_name' => 'BrowserJavaScriptEnabled',
                'display_name'  => self::s2p_t('Browser JavaScript enabled'),
                'type'          => S2P_SDK_VTYPE_BOOL,
                'default'       => true,
            ],
            [
                'name'          => 'browserlanguage',
                'external_name' => 'BrowserLanguage',
                'display_name'  => self::s2p_t('Browser language'),
                'type'          => S2P_SDK_VTYPE_STRING,
                'default'       => '',
            ],
            [
                'name'            => 'browsercolordepth',
                'external_name'   => 'BrowserColorDepth',
                'display_name'    => self::s2p_t('Browser color depth'),
                'type'            => S2P_SDK_VTYPE_INT,
                'default'         => 0,
                'skip_if_default' => true,
            ],
            [
                'name'            => 'browserscreenheight',
                'external_name'   => 'BrowserScreenHeight',
                'display_name'    => self::s2p_t('Browser screen height'),
                'type'            => S2P_SDK_VTYPE_INT,
                'default'         => 0,
                'skip_if_default' => true,
            ],
            [
                'name'            => 'browserscreenwidth',
                'external_name'   => 'BrowserScreenWidth',
                'display_name'    => self::s2p_t('Browser screen width'),
                'type'            => S2P_SDK_VTYPE_INT,
                'default'         => 0,
                'skip_if_default' => true,
            ],
            [
                'name'            => 'browsertimezone',
                'external_name'   => 'BrowserTimeZone',
                'display_name'    => self::s2p_t('Browser timezone'),
                'type'            => S2P_SDK_VTYPE_INT,
                'default'         => 0,
                'skip_if_default' => true,
            ],
        ];
    }
}
