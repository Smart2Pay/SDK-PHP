<?php

    // Detecting current directory path - in certain environment setups you might have to manually provide this path
    if( !($_current_directory_path = __DIR__)
    and !($_current_directory_path = @getcwd()) )
        $_current_directory_path = '.';

    $_current_directory_path = rtrim( $_current_directory_path, '/\\' ).'/';

    include_once( $_current_directory_path . 'classes/S2P_SDK_Error.php' );
    include_once( $_current_directory_path . 'classes/S2P_SDK_Language_Container.php' );
    include_once( $_current_directory_path . 'classes/S2P_SDK_Language.php' );
    include_once( $_current_directory_path . 'classes/S2P_SDK_Module.php' );

    if( !S2P_SDK\S2P_SDK_Module::sdk_init( $_current_directory_path ) )
    {
        echo 'Failed initializing Smart2Pay SDK.';
        exit;
    }

    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Currencies.php' );
    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Countries.php' );
    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Rest_API_Request.php' );
    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Rest_API_Codes.php' );
    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Rest_API.php' );
    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Helper.php' );

    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Values_Source_Methods.php' );
    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Values_Source_Recurring_Methods.php' );
    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Values_Sources_Article_Type.php' );
    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Values_Source.php' );

    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Scope_Variable.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Scope_Structure.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Generic_Error.php' );

    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Status.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Customer.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Address.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Article.php' );

    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Customer_Details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Reference_Details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Preapproval_details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Details.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Token_Details.php');
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchantsite_Details.php' );

    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Preapproval_Request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Preapproval_Response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Preapproval_Response_List.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Request_List.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payment_Response_List.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Types_Response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Types_Response_List.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Refund_Response_List.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Payment_Request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Refund_Request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Refund_Response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Card_Refund_Response_List.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payout_Request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payout_Response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Payout_Response_List.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Method_Validator.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Method_Option.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Method.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Method_List.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_User_Response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_User_Request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchantsite.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchantsite_List.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchant_Request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchant_Response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchant_Create_Request.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Merchant_Create_Response.php' );
    include_once( S2P_SDK_DIR_STRUCTURES . 'S2P_SDK_Structure_Fraud_Details_Response.php' );

    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_API.php' );

    include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Method.php' );
    include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Preapprovals.php' );
    include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Payments.php' );
    include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Cards.php' );
    include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Methods.php' );
    include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Users.php' );
    include_once( S2P_SDK_DIR_METHODS . 'S2P_SDK_Meth_Merchantsites.php' );

    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Notification.php' );
    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Return.php' );

    // R&D stuff...
    // include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Database.php' );
    // include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Database_Wrapper.php' );
    // include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Context.php' );

