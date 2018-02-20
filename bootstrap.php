<?php

    // Detecting current directory path - in certain environment setups you might have to manually provide this path
    if( !($_current_directory_path = __DIR__)
    and !($_current_directory_path = @getcwd()) )
        $_current_directory_path = '.';

    define( 'S2P_SDK_VERSION', '2.0.4' );

    define( 'S2P_SDK_DIR_PATH', $_current_directory_path.'/' );
    define( 'S2P_SDK_DIR_CLASSES', $_current_directory_path.'/classes/' );
    define( 'S2P_SDK_DIR_STRUCTURES', $_current_directory_path.'/structures/' );
    define( 'S2P_SDK_DIR_METHODS', $_current_directory_path.'/methods/' );
    define( 'S2P_SDK_DIR_LANGUAGES', $_current_directory_path.'/languages/' );

    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_error.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_language_container.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_language.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_currencies.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_countries.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_module.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_rest_api_request.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_rest_api_codes.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_rest_api.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_helper.php' );

    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_values_source_methods.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_values_source_recurring_methods.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_values_source_article_type.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_values_source.php' );

    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_scope_variable.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_scope_structure.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_generic_error.php' );

    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_status.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_customer.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_address.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_article.php' );

    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_customer_details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_reference_details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_preapproval_details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_refund_details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_card_details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_card_token_details.php');
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_merchantsite_details.php' );

    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_preapproval_request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_preapproval_response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_preapproval_response_list.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_request_list.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payment_response_list.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_refund_types_response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_refund_types_response_list.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_refund_request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_refund_response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_refund_response_list.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_card_payment_request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_card_refund_request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_card_refund_response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_card_refund_response_list.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payout_request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payout_response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_payout_response_list.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_method_validator.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_method_option.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_method.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_method_list.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_user_response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_user_request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_merchantsite.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_merchantsite_list.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_merchant_request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_merchant_response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_merchant_create_request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_merchant_create_response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 's2p_sdk_structure_fraud_details_response.php' );

    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_api.php' );

    include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_method.php' );
    include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_meth_preapprovals.php' );
    include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_meth_payments.php' );
    include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_meth_cards.php' );
    include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_meth_methods.php' );
    include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_meth_users.php' );
    include_once( S2P_SDK_DIR_METHODS . 's2p_sdk_meth_merchantsites.php' );

    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_notification.php' );
    include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_return.php' );

    // R&D stuff...
    // include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_database.php' );
    // include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_wrapper.php' );
    // include_once( S2P_SDK_DIR_CLASSES . 's2p_sdk_context.php' );

    S2P_SDK\S2P_SDK_Language::set_multi_language( true );

    if(
        !S2P_SDK\S2P_SDK_Language::define_language( S2P_SDK\S2P_SDK_Language::LANG_EN, array(
                    'title' => 'English',
                    'files' => array( S2P_SDK_DIR_LANGUAGES.'en.csv' ),
                ) )
        or

        !S2P_SDK\S2P_SDK_Language::define_language( S2P_SDK\S2P_SDK_Language::LANG_RO, array(
                    'title' => 'Romana',
                    'files' => array( S2P_SDK_DIR_LANGUAGES.'ro.csv' ),
                ) )
    )
    {
        // Do something if we cannot initialize English language
        S2P_SDK\S2P_SDK_Language::language_container()->throw_error();
    } else
    {
        S2P_SDK\S2P_SDK_Language::language_container()->set_current_language( S2P_SDK\S2P_SDK_Language::LANG_EN );
    }

    S2P_SDK\S2P_SDK_Module::st_debugging_mode( false );
    S2P_SDK\S2P_SDK_Module::st_detailed_errors( false );
    S2P_SDK\S2P_SDK_Module::st_throw_errors( false );

    if( @file_exists( S2P_SDK_DIR_PATH.'config.php' ) )
    {
        include_once( S2P_SDK_DIR_PATH.'config.php' );
    } elseif( @file_exists( S2P_SDK_DIR_PATH.'config.inc.php' ) )
    {
        include_once( S2P_SDK_DIR_PATH.'config.inc.php' );
    } else
        die( 'SDK config file not found. Please create Smart2Pay SDK configuration file.' );
