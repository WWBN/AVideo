# Rest API Samples

![Home Image](https://raw.githubusercontent.com/wiki/paypal/PayPal-PHP-SDK/images/homepage.jpg)

These examples are created to experiment with the PayPal-PHP-SDK capabilities. Each examples are designed to demonstrate the default use-cases in each segment.

This sample project is a simple web app that you can explore to understand what each PayPal APIs can do for you. Irrespective of how you [installed your SDK](https://github.com/paypal/PayPal-PHP-SDK/wiki/Installation), you should be able to get the samples running by following the instructions below:

## Viewing Sample Code
You can [view sample source codes here](http://paypal.github.io/PayPal-PHP-SDK/sample/). However, we recommend you run samples locally to get a better idea.

## Instructions

If you are running PHP 5.4 or greater, PHP provides a [ built-in support ]( http://php.net/manual/en/features.commandline.webserver.php) for hosting PHP sites.

Note: The root directory for composer based download would be `vendor` and for direct download it would be `PayPal-PHP-SDK`. Please update the commands accordingly.

1. Run `php -f PayPal-PHP-SDK/paypal/rest-api-sdk-php/sample/index.php` from your project root directory.
2. This would host a PHP server at `localhost:5000`. The output should look something like this:
    
    ```
    <!-- Welcome to PayPal REST SDK -- >
    PHP 5.5.14 Development Server started at Sat Jan 10 14:04:35 2015
    Listening on http://localhost:5000
    Document root is /Users/japatel/Desktop/project/PayPal-PHP-SDK/paypal/rest-api-sdk-php/sample
    Press Ctrl-C to quit.
    ```
3. Open [http://localhost:5000/](http://localhost:5000/) in your web browser, and you should be able to see the sample dashboard.
4. You should see a sample dashboard page as shown below:
![Sample Web](https://raw.githubusercontent.com/wiki/paypal/PayPal-PHP-SDK/images/sample_web.gif)

#### Configuration (Optional)

The sample comes pre-configured with a test account but in case you need to try them against your account, you must
   * Obtain your client id and client secret from the [developer portal](https://developer.paypal.com/webapps/developer/applications/myapps)
   * Update the [bootstrap.php](https://github.com/paypal/PayPal-PHP-SDK/blob/master/sample/bootstrap.php#L29) file with your new client id and secret.

## Alternative Options

There are two other ways you could run your samples, as shown below:

* #### Alternatives: LAMP Stack (All supported PHP Versions)

    * You could host the entire project in your local web server, by using tools like [MAMP](http://www.mamp.info/en/) or [XAMPP](https://www.apachefriends.org/index.html).
    * Once done, you could easily open the samples by opening the matching URL. For e.g.:
`http://localhost/PayPal-PHP-SDK/paypal/rest-api-sdk-php/sample/index.html`

* #### Alternatives: Running on console
    > Please note that there are few samples that requires you to have a working local server, to receive redirects when user accepts/denies PayPal Web flow

    * To run samples itself on console, you need to open command prompt, and direct to samples directory.
    * Execute the sample php script by using `php -f` command as shown below:
    ```bat
php -f payments/CreatePaymentUsingSavedCard.php
    ```

    * The result would be as shown below:
    ![Sample Console](https://raw.githubusercontent.com/wiki/paypal/PayPal-PHP-SDK/images/sample_console.png)

#### Sample App

If you are looking for a full fledged application that uses the new RESTful APIs, check out the Pizza store sample app at https://github.com/paypal/rest-api-sample-app-php

## More help
   * [Going Live](https://github.com/paypal/PayPal-PHP-SDK/wiki/Going-Live)
   * [PayPal-PHP-SDK Home Page](http://paypal.github.io/PayPal-PHP-SDK/)
   * [SDK Documentation](https://github.com/paypal/PayPal-PHP-SDK/wiki)
   * [Sample Source Code](http://paypal.github.io/PayPal-PHP-SDK/sample/)
   * [API Reference](https://developer.paypal.com/webapps/developer/docs/api/)
   * [Reporting Issues / Feature Requests] (https://github.com/paypal/PayPal-PHP-SDK/issues)
   * [Pizza App Using Paypal REST API] (https://github.com/paypal/rest-api-sample-app-php)
