<?php

    include( '../bootstrap.php' );

    //
    // !!! Uncomment following lines if you don't want to create config.php file in SDK root folder. Scenario used in composer installs.
    // !!! Use SDK like this only if you cannot create config.php file in SDK root folder. If you use config.php file you will not have to always
    // !!! have these calls in each entry point that uses the SDK.
    //
    // define( 'S2P_SDK_SITE_ID', '{PROVIDED_SITE_ID}' );
    // define( 'S2P_SDK_API_KEY', '{PROVIDED_API_KEY}' );
    // define( 'S2P_SDK_ENVIRONMENT', 'test' ); // live or test
    //
    // You can also control debugging mode, detailed errors and error throwing
    //
    // Set SDK in debugging mode (or not)
    // S2P_SDK\S2P_SDK_Module::st_debugging_mode( false );
    // display full trace with the error (or not)
    // S2P_SDK\S2P_SDK_Module::st_detailed_errors( false );
    // Favor throwing errors when setting errors in classes (or not)
    // S2P_SDK\S2P_SDK_Module::st_throw_errors( false );
    //

    //
    //
    // !!!!! THIS SCRIPT IS INTEDED TO GIVE YOU A STARTING POINT ON HOW YOU SHOULD HANDLE NOTIFICATIONS
    // !!!!! PLEASE DON'T USE THIS SCRIPT AS NOTIFICATION SCRIPT, INSTEAD COPY IT IN YOUR ENVIRONMENT AND CHANGE IT. (THIS SCRIPT MIGHT CHANGE IN TIME)
    //
    // !!!!! THIS IS THE SCRIPT WHICH HANDLES ALL SERVER COMMUNICATION. THIS COMMUNICATION HAPPENS IN BACKGROUND, SO THIS SCRIPT WILL NOT NEED A FRONT-END INTERFACE.
    // !!!!! THIS SCRIPT WILL ONLY HAVE TO ECHO MESSAGES AS DESCRIBED IN MANUAL FOR EACH NOTIFICATION TYPE RECEIVED.
    //
    //

    define( 'S2P_SDK_NOTIFICATION_IDENTIFIER', microtime( true ) );

    // Change this to false on production sites
    S2P_SDK\S2P_SDK_Notification::logging_enabled( true );

    S2P_SDK\S2P_SDK_Notification::logf( '--- Notification START --------------------', false );

    $notification_params = array();
    $notification_params['auto_extract_parameters'] = true;

    /** @var S2P_SDK\S2P_SDK_Notification $notification_obj */
    if( !($notification_obj = S2P_SDK\S2P_SDK_Module::get_instance( 'S2P_SDK_Notification', $notification_params ))
     or $notification_obj->has_error() )
    {
        if( (S2P_SDK\S2P_SDK_Module::st_has_error() and $error_arr = S2P_SDK\S2P_SDK_Module::st_get_error())
         or (!empty( $notification_obj ) and $notification_obj->has_error() and ($error_arr = $notification_obj->get_error())) )
            $error_msg = 'Error ['.$error_arr['error_no'].']: '.$error_arr['display_error'];
        else
            $error_msg = 'Error initiating notification object.';

        S2P_SDK\S2P_SDK_Notification::logf( $error_msg );
        exit;
    }

    // S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );

    if( !$notification_obj->check_authentication() )
    {
        if( $notification_obj->has_error()
        and ($error_arr = $notification_obj->get_error()) )
            S2P_SDK\S2P_SDK_Notification::logf( 'Error: '.$error_arr['display_error'] );
        else
            S2P_SDK\S2P_SDK_Notification::logf( 'Authentication failed.' );
        exit;
    }

    if( !($notification_type = $notification_obj->get_type())
     or !($notification_title = $notification_obj::get_type_title( $notification_type )) )
    {
        S2P_SDK\S2P_SDK_Notification::logf( 'Unknown notification type.' );
        S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );
        exit;
    }

    S2P_SDK\S2P_SDK_Notification::logf( 'Received notification type [%s].', $notification_title, false );

    switch( $notification_type )
    {
        case $notification_obj::TYPE_PAYMENT:
            if( !($result_arr = $notification_obj->get_array())
             or empty( $result_arr['payment'] ) or !is_array( $result_arr['payment'] ) )
            {
                S2P_SDK\S2P_SDK_Notification::logf( 'Couldn\'t extract payment object.' );
                S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );
                exit;
            }

            $payment_arr = $result_arr['payment'];

            if( empty( $payment_arr['merchanttransactionid'] )
             or empty( $payment_arr['status'] ) or empty( $payment_arr['status']['id'] ) )
            {
                S2P_SDK\S2P_SDK_Notification::logf( 'MerchantTransactionID or Status not provided.' );
                S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );
                exit;
            }

            if( !isset( $payment_arr['amount'] ) or !isset( $payment_arr['currency'] ) )
            {
                S2P_SDK\S2P_SDK_Notification::logf( 'Amount or Currency not provided.' );
                S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );
                exit;
            }

            //
            // TODO: Retrieve transaction with ID $payment_arr['merchanttransactionid'] from database
            //       and make sure $payment_arr['amount'] and $payment_arr['currency'] are same with database result
            //

            if( !($status_title = S2P_SDK\S2P_SDK_Meth_Payments::valid_status( $payment_arr['status']['id'] )) )
                $status_title = '(unknown)';

            S2P_SDK\S2P_SDK_Notification::logf( 'Received '.$status_title.' notification for transaction '.$payment_arr['merchanttransactionid'].'.', false );

            // Update database according to payment status
            switch( $payment_arr['status']['id'] )
            {
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_OPEN:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Transaction is open.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_PENDING_CUSTOMER:
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_PENDING_PROVIDER:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Transaction is pending.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_SUCCESS:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Transaction is successful.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_CANCELLED:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Transaction is cancelled.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_FAILED:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Transaction failed.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_EXPIRED:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Transaction expired.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_PROCESSING:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Processing transaction...', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_AUTHORIZED:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Transaction authorized.', false );
                break;
            }
        break;

        case $notification_obj::TYPE_PREAPPROVAL:
            if( !($result_arr = $notification_obj->get_array())
             or empty( $result_arr['preapproval'] ) or !is_array( $result_arr['preapproval'] ) )
            {
                S2P_SDK\S2P_SDK_Notification::logf( 'Couldn\'t extract preapproval object.' );
                S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );
                exit;
            }

            $preapproval_arr = $result_arr['preapproval'];

            if( empty( $preapproval_arr['merchantpreapprovalid'] )
             or empty( $preapproval_arr['status'] ) or empty( $preapproval_arr['status']['id'] ) )
            {
                S2P_SDK\S2P_SDK_Notification::logf( 'MerchantPreapprovalID or Status not provided.' );
                S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );
                exit;
            }

            if( !($status_title = S2P_SDK\S2P_SDK_Meth_Preapprovals::valid_status( $preapproval_arr['status']['id'] )) )
                $status_title = '(unknown)';

            S2P_SDK\S2P_SDK_Notification::logf( 'Received '.$status_title.' notification for preapproval '.$preapproval_arr['merchantpreapprovalid'].'.', false );

            // Update database according to payment status
            switch( $preapproval_arr['status']['id'] )
            {
                case S2P_SDK\S2P_SDK_Meth_Preapprovals::STATUS_PENDING:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Preapproval pending.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Preapprovals::STATUS_OPEN:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Proapproval open. You can initiate transactions with preapproval ID '.$preapproval_arr['id'].'.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Preapprovals::STATUS_CLOSEDBYCUSTOMER:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Preapproval is closed.', false );
                break;
            }
        break;

        case $notification_obj::TYPE_REFUND:
            if( !($result_arr = $notification_obj->get_array())
             or empty( $result_arr['refund'] ) or !is_array( $result_arr['refund'] ) )
            {
                S2P_SDK\S2P_SDK_Notification::logf( 'Couldn\'t extract refund object.' );
                S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );
                exit;
            }

            $refund_arr = $result_arr['refund'];

            if( empty( $refund_arr['merchanttransactionid'] )
             or empty( $refund_arr['status'] ) or empty( $refund_arr['status']['id'] ) )
            {
                S2P_SDK\S2P_SDK_Notification::logf( 'MerchantTransactionID or Status not provided.' );
                S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );
                exit;
            }

            if( !($status_title = S2P_SDK\S2P_SDK_Meth_Payments::valid_status( $refund_arr['status']['id'] )) )
                $status_title = '(unknown)';

            S2P_SDK\S2P_SDK_Notification::logf( 'Received '.$status_title.' notification for refund '.$refund_arr['merchanttransactionid'].'.', false );

            // Update database according to payment status
            switch( $refund_arr['status']['id'] )
            {
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_OPEN:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Refund is open.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_PENDING_CUSTOMER:
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_PENDING_PROVIDER:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Refund is pending.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_SUCCESS:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Refund is successful.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_CANCELLED:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Refund is cancelled.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_FAILED:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Refund failed.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_EXPIRED:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Refund expired.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_PROCESSING:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Refund transaction...', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_AUTHORIZED:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Refund authorized.', false );
                break;
            }
        break;

        case $notification_obj::TYPE_DISPUTE:
            if( !($result_arr = $notification_obj->get_array())
             or empty( $result_arr['dispute'] ) or !is_array( $result_arr['dispute'] ) )
            {
                S2P_SDK\S2P_SDK_Notification::logf( 'Couldn\'t extract dispute object.' );
                S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );
                exit;
            }

            $dispute_arr = $result_arr['dispute'];

            if( empty( $dispute_arr['id'] ) or empty( $dispute_arr['paymentid'] )
             or empty( $dispute_arr['status'] ) or empty( $dispute_arr['status']['id'] ) )
            {
                S2P_SDK\S2P_SDK_Notification::logf( 'ID, PaymentID or Status not provided.' );
                S2P_SDK\S2P_SDK_Notification::logf( 'Input buffer: '.$notification_obj->get_input_buffer(), false );
                exit;
            }

            if( !($status_title = S2P_SDK\S2P_SDK_Meth_Payments::valid_status( $dispute_arr['status']['id'] )) )
                $status_title = '(unknown)';

            S2P_SDK\S2P_SDK_Notification::logf( 'Received '.$status_title.' notification for dispute #'.$dispute_arr['id'].', payment #'.$dispute_arr['paymentid'].'.', false );

            // Update database according to dispute status
            switch( $dispute_arr['status']['id'] )
            {
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_OPEN:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Dispute is open.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_DISPUTE_WON:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Dispute won.', false );
                break;
                case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_DISPUTE_LOST:
                    S2P_SDK\S2P_SDK_Notification::logf( 'Dispute lost.', false );
                break;
            }
        break;
    }

    if( $notification_obj->respond_ok() )
        S2P_SDK\S2P_SDK_Notification::logf( '--- Sent OK -------------------------------', false );

    else
    {
        if( $notification_obj->has_error()
        and ($error_arr = $notification_obj->get_error()) )
            S2P_SDK\S2P_SDK_Notification::logf( 'Error: '.$error_arr['display_error'] );
        else
            S2P_SDK\S2P_SDK_Notification::logf( 'Couldn\'t send ok response.' );
    }


