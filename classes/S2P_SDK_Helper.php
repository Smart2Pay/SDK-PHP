<?php

namespace S2P_SDK;

class S2P_SDK_Helper extends S2P_SDK_Language
{

    public static function get_php_input()
    {
        static $input = false;

        if( $input !== false )
            return $input;

        if( ($input = @file_get_contents( 'php://input' )) === false )
            return false;

        return $input;
    }

}
