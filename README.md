# Smart2Pay SDK

**NB**: If you installed our SDK using composer, please read the note at the end of the file.

For quick information about available SDK methods and functionalities, please open `{SDK directory}/play.php` script in a browser (requires a web server).

For live demo of methods and functionalities available in current version of SDK, please open `{SDK directory}/demo.php` script in a browser (requires a web server).

Please note that in order to test a full end-to-end transaction you will require a valid Smart2Pay test account which you can obtain at [https://www.smart2pay.com/en/SignUpStart](https://www.smart2pay.com/en/SignUpStart). After you registered a test account, use API Key and Site ID found at _Technical Integration_ > _Integration Roadmap_ > _Integration Site_ and configure your SDK by copying `config.dist.php` to `config.php` and fill in `S2P_SDK_SITE_ID`, `S2P_SDK_API_KEY` and `S2P_SDK_ENVIRONMENT` constants.


#### Creating your test merchant account
1. Go to [https://www.smart2pay.com/en/SignUpStart](https://www.smart2pay.com/en/SignUpStart) and complete the form. Make sure you tick the checkbox _REST API interface_ to let system know you will integrate SDK solution. If you will also use any e-commerce plugins specify this by ticking plugins checkbox.
2. Log into your account and go to _Technical Integration_ > _Integration Roadmap_ > _Integration Site_.
3. You should use Site ID and API Ke
4. y found on that page to fill constants in `config.php` of your test SDK environment.


#### Configuring your SDK
1. Copy `config.dist.php` file located in root directory of your SDK to `config.php` and edit the file.
2. Paste Site ID (from _Technical Integration_ > _Integration Roadmap_ > _Integration Site_) in constant definition `S2P_SDK_SITE_ID` like below:

    ```php
        if( !defined( 'S2P_SDK_SITE_ID' ) )
            define( 'S2P_SDK_SITE_ID', 'SiteID_of_your_Integration_Site' );
    ```
3. Paste API Key (from _Technical Integration_ > _Integration Roadmap_ > _Integration Site_) in constant definition `S2P_SDK_API_KEY` like below:

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
            define( 'S2P_SDK_PAYMENT_RETURN_URL', 'https://www.myshop.com/sdk/samples/_return.php' );
    ```


Once all above steps are completed you can test a full end-to-end transaction and check log files generated.

`demo.php` script will generate logs when redirected back to return page in `log_return.log` and also for notifications in `log_demo.log`. Please make sure PHP has rights to create and write in these files located in root of SDK directory. If PHP doesn't have creation rights on SDK directory create these two log files first and give PHP write rights on them.

For quick samples on how you should implement SDK methods and functionalities please check `samples` directory.

#### Composer installs note

If you installed our SDK using composer and you don't want to create a custom `config.php` file, you will have to define `S2P_SDK_SITE_ID`, `S2P_SDK_API_KEY` and `S2P_SDK_ENVIRONMENT` constants in SDK wrapper script. Please check files in `samples` directory.

There were reports about composer deleting `config.php` file in SDK root dir when updating the SDK. To overcome this, you can define `S2P_SDK_CONFIG_PATH` constant in your wrapper script which contains directory where `config.php` file resides in your project.
