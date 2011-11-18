# PHP Eventbrite API Client Library - OAuth2.0 examples # 
---------------------------------------------------------
## Requirements: ##
### API key ###
Eventbrite API keys are available here: http://www.eventbrite.com/api/key/
### Client Secret ###
Your API_Key's Client secret is available on the same page.  Keep it secret.  Be careful not to expose it to your users or check it in to publicly available source code.
### Configuring your redirect_uri ###
You must also configure your API_key's redirect_uri on http://www.eventbrite.com/api/key/, pointing it to the URL on your site where you expect a user to complete their OAuth2 authorization.  Or, point it to any URL on your site where our loginWidget is available.

## Simple implementation example: ##
Get OAuth2.0 done in three easy steps.  Check below for a more advanced implementation example that shows how to integrate with your existing storage and templating systems.

### 1. Download and install Eventbrite's PHP API client ###
Grab Eventbrite's latest PHP API client and add it to your app's source code: https://raw.github.com/ryanjarvinen/eventbrite.php/master/Eventbrite.php

### 2. Load the API Client library ###

    require_once 'Eventbrite.php';

### 2a. (optional) This example uses PHP's built-in $_SESSION storage:  ###
See our advanced implementation example for notes on how to use other data-stores.
If you do not already have support for session storage enabled, then you may need to turn it on:

    session_start();

### 3. Generate the Eventbrite LoginWidget HTML ###
Add your authentication tokens to make this widget example work:

    <?= Eventbrite::loginWidget(array('app_key'=>'YOUR_API_KEY', 
                                'client_secret'=>'YOUR_CLIENT_SECRET')); ?>

This example code is also bundled with our API Client library:
https://github.com/ryanjarvinen/eventbrite.php/blob/master/examples/oauth2-login-example.php

## API Client methods for working with OAuth2.0 ##
See Eventbrite's [API Docs](http://developer.eventbrite.com/doc) for more information about available API Client methods.

### Eventbrite::loginWidget ###
The loginWidget method is the quickest way to acheive OAuth2.0 integration for Eventbrite. It includes a lot of great defaults, and the ability to customize when needed.  By default, PHP's $_SESSION store will be used to save your user's access_token.

#### Function overview: ####
string <b>Eventbrite::loginWidget</b>( array $options [, callback $get_token, callback $save_token, callback $delete_token], callback $renderer )

#### Parameters: ####
* options - an array containing the following:
    * app_key - (Required) A string containing your API key - sign up here: http://www.eventbrite.com/api/key/
    * client_secret - (Required) A string containing this API key's client_secret (available on the same page as your API Key)
    * access_code - (Optional) A string containing the user's access_code. An OAuth2.0 access_code is an intermediary, temporary token that can be exchanged for an access_token.  If you know the access_code, you can supply it here.  By default, this widget will attempt to autodetect this information by checking the REQUEST for a "code" parameter. This should work fine as long as the widget is available at the same URL that your "redirect_uri" is pointed toward.
    * logout_link - (Optional) A string containing the URL that should trigger a "logout" or "deleteToken" action.  By default, the widget is configured to delete a user's access_token whenever the querystring contains "eb_logout=true".  This should work on any page where the widget is available.  See our "Advanced implementation" example for information on how to incorporate this into your existing authentication scheme.
    * error_message - (Optional) A string containing an OAuth2.0 error response from Eventbrite.  By default, the widget is configured to auto-detect this information by reading from $_REQUEST['error'].  If you wish to disable error_message auto-detection, set this value to "disabled".
* get_token - (Optional) A callback describing how to retrieve the current user's OAuth2.0 access_token from your site's data store.
* save_token - (Optional) A callback describing how to save the current user's OAuth2.0 access_token in your site's data store.
* delete_token - (Optional) A callback describing how to remove the current user's OAuth2.0 access_token from your site's data store.
* renderer - (Optional) A callback describing how to render the widget data as HTML.  If you have your own templating system that you would like to work with, you can pass the resulting HTML into your template as a string, or set this value to "disabled" to signal that you would like the response to include an array of strings instead of HTML.  For more information on how to write your own render callback, see the widgetHTML function below.  By default, the widgetHTML function will be used to generate HTML for you.

# Resources: #
* <a href="http://eventbrite.github.com/">Eventbrite on GitHub</a>
* <a href="http://developer.eventbrite.com/doc/">API Documentation</a>
* <a href="http://developer.eventbrite.com/doc/getting-started/">API Getting-Started Guide</a>
* <a href="http://developer.eventbrite.com/terms/">Eventbrite API terms and usage limitations</a>
* <a href="http://developer.eventbrite.com/news/branding/">Eventbrite Branding Guidelines</a>
