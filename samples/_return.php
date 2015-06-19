<?php

    include( '../bootstrap.php' );


    //
    //
    // !!!!! DO NOT UPDATE TRANSACTION STATUSES IN THIS SCRIPT! WE WILL SEND TRANSACTION DETAILS TO YOUR NOTIFICATION SCIPRT (SETUP IN OUR DASHBOARD)
    // !!!!! AND YOU MUST UPDATE TRANSACTION AND ORDER STATUS IN YOUR NOTIFICATION SCRIPT
    // !!!!!  THIS SCRIPT IS INTENDED TO NOTIFY YOUR CLIENT ABOUT CURRENT STATUS OF TRANSACTION.
    //
    // Please note that transaction can be in Open or Processing status which means a final status notification will follow
    //
    //


    include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_return.inc.php' );

    $return_params = array();
    $return_params['auto_extract_parameters'] = true;

    /** @var S2P_SDK\S2P_SDK_Return $return_obj */
    if( !($return_obj = S2P_SDK\S2P_SDK_Module::get_instance( 'S2P_SDK_Return', $return_params )) )
    {
        if( ($error_arr = S2P_SDK\S2P_SDK_Module::st_get_error()) )
            echo 'Error ['.$error_arr['error_no'].']: '.$error_arr['display_error'];
        else
            echo 'Error initiating return object.';

        exit;
    }

    if( !($return_parameters = $return_obj->get_parameters()) )
        die( 'Couldn\'t extract return parameters.' );

    if( empty( $return_parameters['MerchantTransactionID'] ) )
        die( 'Unknown transaction' );

    echo 'Transaction ['.$return_parameters['MerchantTransactionID'].']: ';

    switch( $return_parameters['data'] )
    {
        default:
            echo 'unknown transaction status ('.$return_parameters['data'].')';
        break;

        case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_OPEN:
            echo 'Open (not finalized yet)';
        break;

        case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_SUCCESS:
            echo 'Success';
        break;

        case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_CANCELLED:
            echo 'Cancelled';
        break;

        case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_FAILED:
            echo 'Failed';
        break;

        case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_EXPIRED:
            echo 'Expired';
        break;

        case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_PROCESSING:
            echo 'Processing (not finalized yet)';
        break;

        case S2P_SDK\S2P_SDK_Meth_Payments::STATUS_AUTHORIZED:
            echo 'Authorized';
        break;
    }

    if( !empty( $return_parameters['extra_info']['has_data'] ) )
    {
        echo '<br/><br/><hr/><br/><br/>'.
             'Some extra information about transaction which might help customer finalize the payment:';

        $extra_info = $return_parameters['extra_info'];

        if( isset( $extra_info['has_data'] ) )
            unset( $extra_info['has_data'] );

        echo '<pre>'; var_dump( $extra_info ); echo '</pre>';
    }
