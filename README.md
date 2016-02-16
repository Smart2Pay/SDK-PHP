# Smart2Pay SDK

For quick information about available SDK methods and functionalities, please open play.php script in a browser (requires a web server).

For demo of methods and functionalities available in current version of SDK, please open demo.php script in a browser (requires a web server).

Please note that in order to test a full end-to-end transaction you will require a valid Smart2Pay test account which you can obtain at https://dashboardtest.smart2pay.com/Account/Register and also you should configure your SDK.

#### Creating your test merchant account
1. Go to [https://dashboardtest.smart2pay.com/Account/Register] and complete the form.
2. You will be contacted by our support to settle which payment methods you want activated for your test account.
3. Once your test merchant account is activated, you should login and then go to Configuration > REST API in order to create a test site.
4. Click + sign and complete Site form: **URL** should be something meaningful for you (eg. http://www.myshop.com), **Notification URL** provide full URL to the script located in samples/_notification.php of your SDK (eg. http://www.myshop.com/sdk/samples/_notification.php) and then click on **OK**.
5. Once your site is configured you should take Apikey of generated site by clicking "Key/ABC" icon in Rest API sites table (make sure you copy all string in that column).


#### Configuring your SDK
1. Copy **config.inc.dist.php** file located in root directory of your SDK to **config.inc.php** and edit config.inc.php file.
2. Paste Apikey of generated site in constant definition *S2P_SDK_API_KEY* like below:

    ```php
        if( !defined( 'S2P_SDK_API_KEY' ) )
            define( 'S2P_SDK_API_KEY', 'Apikey_generated_above' );
    ```
3. Configure environment to test:
 
    ```php
        if( !defined( 'S2P_SDK_ENVIRONMENT' ) )
            define( 'S2P_SDK_ENVIRONMENT', 'test' ); // live or test
    ```
4. Setup return URL. This URL is the location where end-user will be redirected after a transaction finishes (successful or not):
 
    ```php
        if( !defined( 'S2P_SDK_PAYMENT_RETURN_URL' ) )
            define( 'S2P_SDK_PAYMENT_RETURN_URL', 'http://www.myshop.com/sdk/samples/_return.php' );
    ```


Once all above steps are completed you can test a full end-to-end transaction and check log files generated.

demo.php script will generate logs when redirected back to return page in log_return.log and also for notifications in log_demo.log.

For quick samples of how you should implement SDK methods and functionalities please check samples directory.
