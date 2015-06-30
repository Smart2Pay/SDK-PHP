<?php

    // Merchant API Key or API Key generated for a site
    if( !defined( 'S2P_SDK_API_KEY' ) )
        define( 'S2P_SDK_API_KEY', '{PROVIDED_API_KEY}' );

    // Tells SDK where to send the request (test server or live server)
    if( !defined( 'S2P_SDK_ENVIRONMENT' ) )
        define( 'S2P_SDK_ENVIRONMENT', '' ); // live or test

    // Tells SDK default return URL to be used when payments are initiated.
    // If return url parameter is not specified in payment method initialization parameters SDK will check this constant
    // regexp: ^(http(s)?(:\/\/|%3A%2F%2F).+){1,512}$
    if( !defined( 'S2P_SDK_PAYMENT_RETURN_URL' ) )
        define( 'S2P_SDK_PAYMENT_RETURN_URL', '' );

    if( !S2P_SDK\S2P_SDK_Language::language_container()->get_current_language() )
        S2P_SDK\S2P_SDK_Language::language_container()->set_current_language( S2P_SDK\S2P_SDK_Language::LANG_EN );
