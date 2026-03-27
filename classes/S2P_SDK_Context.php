<?php
namespace S2P_SDK;

/**
 * Contains any values which create dependencies
 *
 * Class S2P_SDK_Context
 */
class S2P_SDK_Context extends S2P_SDK_Language
{
    /** @var string */
    private static $_country = '';

    /** @var int */
    private static $_methodid = 0;

    /** @var string */
    private static $_currency = '';

    public function __construct()
    {
        parent::__construct();

        $this->reset_context();
    }

    public function reset_context()
    {
        self::$_country = '';
        self::$_methodid = 0;
        self::$_currency = '';
    }
}
