<?php

namespace S2P_SDK;

class S2P_SDK_Rest_API_Codes extends S2P_SDK_Language
{
    public static function success_codes()
    {
        return array( 200, 201 );
    }

    public static function valid_code( $code )
    {
        $code = (int)$code;
        if( !($all_codes = self::rest_codes()) or empty( $all_codes[$code] ) )
            return false;

        return $all_codes[$code];
    }

    public static function rest_codes()
    {
        return array(

            0 => self::s2p_t( 'Host not found / Timed out' ),

            100 => self::s2p_t( 'Continue' ),
            101 => self::s2p_t( 'Switching Protocols' ),
            102 => self::s2p_t( 'Processing (WebDAV)' ),

            200 => self::s2p_t( 'OK' ),
            201 => self::s2p_t( 'Created' ),
            202 => self::s2p_t( 'Accepted' ),
            203 => self::s2p_t( 'Non-Authoritative Information' ),
            204 => self::s2p_t( 'No Content' ),
            205 => self::s2p_t( 'Reset Content' ),
            206 => self::s2p_t( 'Partial Content' ),
            207 => self::s2p_t( 'Multi-Status (WebDAV)' ),
            208 => self::s2p_t( 'Already Reported (WebDAV)' ),
            226 => self::s2p_t( 'IM Used' ),

            300 => self::s2p_t( 'Multiple Choices' ),
            301 => self::s2p_t( 'Moved Permanently' ),
            302 => self::s2p_t( 'Found' ),
            303 => self::s2p_t( 'See Other' ),
            304 => self::s2p_t( 'Not Modified' ),
            305 => self::s2p_t( 'Use Proxy' ),
            306 => self::s2p_t( '(Unused)' ),
            307 => self::s2p_t( 'Temporary Redirect' ),
            308 => self::s2p_t( 'Permanent Redirect (experimental)' ),

            400 => self::s2p_t( 'Bad Request' ),
            401 => self::s2p_t( 'Unauthorized' ),
            402 => self::s2p_t( 'Payment Required' ),
            403 => self::s2p_t( 'Forbidden' ),
            404 => self::s2p_t( 'Not Found' ),
            405 => self::s2p_t( 'Method Not Allowed' ),
            406 => self::s2p_t( 'Not Acceptable' ),
            407 => self::s2p_t( 'Proxy Authentication Required' ),
            408 => self::s2p_t( 'Request Timeout' ),
            409 => self::s2p_t( 'Conflict' ),
            410 => self::s2p_t( 'Gone' ),
            411 => self::s2p_t( 'Length Required' ),
            412 => self::s2p_t( 'Precondition Failed' ),
            413 => self::s2p_t( 'Request Entity Too Large' ),
            414 => self::s2p_t( 'Request-URI Too Long' ),
            415 => self::s2p_t( 'Unsupported Media Type' ),
            416 => self::s2p_t( 'Requested Range Not Satisfiable' ),
            417 => self::s2p_t( 'Expectation Failed' ),
            418 => self::s2p_t( 'I\'m a teapot (RFC 2324)' ),
            420 => self::s2p_t( 'Enhance Your Calm (Twitter)' ),
            422 => self::s2p_t( 'Unprocessable Entity (WebDAV)' ),
            423 => self::s2p_t( 'Locked (WebDAV)' ),
            424 => self::s2p_t( 'Failed Dependency (WebDAV)' ),
            425 => self::s2p_t( 'Reserved for WebDAV' ),
            426 => self::s2p_t( 'Upgrade Required' ),
            428 => self::s2p_t( 'Precondition Required' ),
            429 => self::s2p_t( 'Too Many Requests' ),
            431 => self::s2p_t( 'Request Header Fields Too Large' ),
            444 => self::s2p_t( 'No Response (Nginx)' ),
            449 => self::s2p_t( 'Retry With (Microsoft)' ),
            450 => self::s2p_t( 'Blocked by Windows Parental Controls (Microsoft)' ),
            499 => self::s2p_t( 'Client Closed Request (Nginx)' ),

            500 => self::s2p_t( 'Internal Server Error' ),
            501 => self::s2p_t( 'Not Implemented' ),
            502 => self::s2p_t( 'Bad Gateway' ),
            503 => self::s2p_t( 'Service Unavailable' ),
            504 => self::s2p_t( 'Gateway Timeout' ),
            505 => self::s2p_t( 'HTTP Version Not Supported' ),
            506 => self::s2p_t( 'Variant Also Negotiates (Experimental)' ),
            507 => self::s2p_t( 'Insufficient Storage (WebDAV)' ),
            508 => self::s2p_t( 'Loop Detected (WebDAV)' ),
            509 => self::s2p_t( 'Bandwidth Limit Exceeded (Apache)' ),
            510 => self::s2p_t( 'Not Extended' ),
            511 => self::s2p_t( 'Network Authentication Required' ),
            598 => self::s2p_t( 'Network read timeout error' ),
            599 => self::s2p_t( 'Network connect timeout error' ),

        );
    }
}
