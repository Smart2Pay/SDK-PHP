<?php

    include_once( 'bootstrap.php' );

    include_once( S2P_SDK_DIR_CLASSES . 'S2P_SDK_Demo.php' );

    if( !($demo = new S2P_SDK\S2P_SDK_Demo())
     or $demo->has_error() )
    {
        if( $demo->has_error() )
            $error_arr = $demo->get_error();
        else
            $error_arr = S2P_SDK\S2P_SDK_Module::st_get_error();

        if( !empty( $error_arr['display_error'] ) )
            die( 'Error initiating demo: '.$error_arr['display_error'] );

        die( 'Error initiating demo.' );
    }

    // Uncomment following line and provide full URL path to SDK root directory
    // $demo->base_url( '{FULL_URL_TO_SDK_ROOT_DIRECTORY}' ); // eg. - http://www.example.com/path/sdk/

    $post_arr = $demo::extract_post_data();

    $submit_params = array();
    $submit_params['post_arr'] = $post_arr;

    if( !($submit_result = $demo->handle_submit( $submit_params )) )
    {
        $submit_result = $demo::default_submit_result();
        if( $demo->has_error() and ($error_arr = $demo->get_error()) )
            $submit_result['errors_arr'] = array( $error_arr['display_error'] );
        else
            $submit_result['errors_arr'] = array( $demo::s2p_t( 'Error handling form submit.' ) );
    }

    $form_params = array();
    $form_params['post_params'] = $post_arr;
    $form_params['form_action_suffix'] = 'demo.php';
    $form_params['submit_result'] = $submit_result;
    $form_params['hidden_form'] = false;

    $demo->display_header( $form_params );

    if( ($form_result = $demo->get_form( $form_params )) )
    {
        if( !empty( $form_result['form_messages'] ) )
        {
            ?><div class="s2p_form"><?php
            if( !empty( $form_result['form_messages']['errors_arr'] ) and is_array( $form_result['form_messages']['errors_arr'] ) )
            {
                ?><div class="errors_container"><?php
                foreach( $form_result['form_messages']['errors_arr'] as $key => $error )
                {
                    ?><div class="error_text"><?php echo $error?></div><?php
                }
                ?></div><?php
            }

            if( !empty( $form_result['form_messages']['success_arr'] ) and is_array( $form_result['form_messages']['success_arr'] ) )
            {
                ?><div class="success_container"><?php
                foreach( $form_result['form_messages']['success_arr'] as $key => $error )
                {
                    ?><div class="success_text"><?php echo $error?></div><?php
                }
                ?></div><?php
            }

            if( !empty( $form_result['form_messages']['warnings_arr'] ) and is_array( $form_result['form_messages']['warnings_arr'] ) )
            {
                ?><div class="warnings_container"><?php
                foreach( $form_result['form_messages']['warnings_arr'] as $key => $error )
                {
                    ?><div class="warning_text"><?php echo $error?></div><?php
                }
                ?></div><?php
            }
            ?></div><?php
        }

        echo $form_result['buffer'];

        if( !empty( $form_params['hidden_form'] ) )
        {
            ?>
            <div style="width: 800px; display:block; margin: 0 auto; text-align: center;">

                <input type="button" onclick="document.<?php echo $form_result['form_name']?>.submit();" id="s2p_demo_outside_submit" value="Submit form" style="margin: 10px 0;" />

            </div>

            <?php
            if( ($call_result = $form_result['call_result']) )
            {
                ?>
                <div id="api_result" style="width: 800px; display:block; margin: 0 auto;">

                    <div class="http_headers_code">
                        <div class="http_headers_code_title"><?php echo S2P_SDK\S2P_SDK_Demo::s2p_t( 'Request headers' );?></div>
                        <?php echo trim( $call_result['request']['request_details']['request_header'] );?>
                    </div>

                    <div class="http_headers_code">
                        <div class="http_headers_code_title">
                            <a href="javascript:void(0);" onclick="toggle_container( 's2p_api_request_body' )"><?php echo S2P_SDK\S2P_SDK_Demo::s2p_t( 'Request body' );?></a>
                        </div>
                        <div id="s2p_api_request_body" style="display: none;">
                            <div id="s2p_api_request_body_raw_toggler">&laquo;
                                <a href="javascript:void(0)" onclick="toggle_container( 's2p_api_request_body_raw' );toggle_container( 's2p_api_request_body_formatted' );"><?php echo S2P_SDK\S2P_SDK_Demo::s2p_t( 'Raw / Formatted response' )?></a> &raquo;
                            </div>
                            <div id="s2p_api_request_body_raw" style="display: block;"><?php echo( empty( $call_result['request']['request_buffer'] ) ? '(empty)' : nl2br( trim( $call_result['request']['request_buffer'] ) ) );?></div>
                            <div id="s2p_api_request_body_formatted" style="display: none;"><?php echo( empty( $call_result['request']['request_buffer'] ) ? '(empty)' : nl2br( S2P_SDK\S2P_SDK_Demo::json_display( trim( $call_result['request']['request_buffer'] ) ) ) );?></div>
                        </div>
                    </div>

                    <div class="http_headers_code">
                        <div class="http_headers_code_title"><?php echo S2P_SDK\S2P_SDK_Demo::s2p_t( 'Response headers' );?></div>
                        <?php
                            if( !empty( $call_result['request']['response_headers'] ) and is_array( $call_result['request']['response_headers'] ) )
                            {
                                foreach( $call_result['request']['response_headers'] as $header_key => $header_val )
                                {
                                    if( !is_numeric( $header_key ) )
                                        echo $header_key . ': ';

                                    echo $header_val . "\n<br/>";
                                }
                            }
                        ?>
                    </div>

                    <div class="http_headers_code">
                        <div class="http_headers_code_title">
                            <a href="javascript:void(0);" onclick="toggle_container( 's2p_api_response_body' )"><?php echo S2P_SDK\S2P_SDK_Demo::s2p_t( 'Response body' );?></a>
                        </div>
                        <div id="s2p_api_response_body" style="display: none;"><?php echo( empty( $call_result['request']['response_buffer'] ) ? '(empty)' : nl2br( trim( $call_result['request']['response_buffer'] ) ) );?></div>
                    </div>

                    <div class="http_headers_code">
                        <div class="http_headers_code_title"><?php echo S2P_SDK\S2P_SDK_Demo::s2p_t( 'Processed response (array)' );?></div>
                        <?php if( empty( $call_result['response']['response_array'] ) )
                            echo '(empty)';
                        else
                        {
                            ob_start();
                            var_dump( $call_result['response']['response_array'] );
                            $buf = ob_get_clean();

                            echo nl2br( str_replace( '  ', ' &nbsp;', $buf ) );
                        }
                        ?>
                    </div>

                </div>
            <?php
            }
        }
    }

    else
    {
        ?><p><?php
        if( ($error_arr = $demo->get_error()) )
            echo $error_arr['display_error'];
        else
            echo 'Error initiating form.';
        ?></p><?php
    }

    $demo->display_footer( $form_params );
