# Smart2Pay SDK

**NB**: If you installed our SDK using composer, please read the note at the end of the file.

For quick information about available SDK methods and functionalities, please open _{SDK directory}/play.php_ script in a browser (requires a web server).

For live demo of methods and functionalities available in current version of SDK, please open _{SDK directory}/demo.php_ script in a browser (requires a web server).

Please note that in order to test a full end-to-end transaction you will require a valid Smart2Pay test account which you can obtain at [https://docs.smart2pay.com/s2p-register/]. After you registered a test account at docs.smart2pay.com, use API Key and Site ID found at Getting Started > Integration Roadmap > Integration Site and configure your SDK by copying config.dist.php to config.php and provide required variables found in config.php.


#### Creating your test merchant account
1. Go to [https://docs.smart2pay.com/s2p-register/] and complete the form. Make sure you tick the checkbox _REST API interface_ to let system know you will integrate SDK solution. If you will also use any e-commerce plugins specify this by ticking plugins checkbox.
2. You will receive an email which will setup a password for your account.
3. Log into your account and go to Getting Started > Integration Roadmap > Integration Site.
6. You should use Site ID and API Key found on that page to configure your test SDK environment.


#### Configuring your SDK
1. Copy **config.dist.php** file located in root directory of your SDK to **config.php** and edit config.php file.
2. Paste Site ID (from Getting Started > Integration Roadmap > Integration Site) in constant definition *S2P_SDK_SITE_ID* like below:

    ```php
        if( !defined( 'S2P_SDK_SITE_ID' ) )
            define( 'S2P_SDK_SITE_ID', 'SiteID_of_your_Integration_Site' );
    ```
3. Paste API Key (from Getting Started > Integration Roadmap > Integration Site) in constant definition *S2P_SDK_API_KEY* like below:

    ```php
        if( !defined( 'S2P_SDK_API_KEY' ) )
            define( 'S2P_SDK_API_KEY', 'APIKey_of_your_Integration_Site' );
    ```
4. Configure environment to test:
 
    ```php
        if( !defined( 'S2P_SDK_ENVIRONMENT' ) )
            define( 'S2P_SDK_ENVIRONMENT', 'test' ); // live or test
    ```
5. When environment is set to custom, you can provide a custom REST API base URL (used for debugging purposes). In production leave this empty:

    ```php
        if( !defined( 'S2P_SDK_CUSTOM_BASE_URL' ) )
            define( 'S2P_SDK_CUSTOM_BASE_URL', '' );
    ```
6. Setup return URL. This URL is the location where end-user will be redirected after a transaction finishes (successful or not):
 
    ```php
        if( !defined( 'S2P_SDK_PAYMENT_RETURN_URL' ) )
            define( 'S2P_SDK_PAYMENT_RETURN_URL', 'http://www.myshop.com/sdk/samples/_return.php' );
    ```


Once all above steps are completed you can test a full end-to-end transaction and check log files generated.

demo.php script will generate logs when redirected back to return page in log_return.log and also for notifications in log_demo.log. Please make sure PHP has rights to create and write in these files located in root of SDK directory. If PHP doesn't have creation rights on SDK directory create these two log files first and give PHP write rights on them.

For quick samples on how you should implement SDK methods and functionalities please check samples directory.

#### Composer installs note

If you installed our SDK using composer and you don't want to create a custom config.php file, you will have to define _S2P_SDK_SITE_ID_, _S2P_SDK_API_KEY_ and _S2P_SDK_ENVIRONMENT_ constants in SDK wrapper script. Please check files in _samples_ directory.

There were reports about composer deleting _config.php_ file in SDK root dir when updating the SDK. To overcome this, you can define _S2P_SDK_CONFIG_PATH_ constant in your wrapper script which contains directory where _config.php_ file resides in your project.
