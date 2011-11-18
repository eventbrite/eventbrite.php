<?php
# Getting Started with OAuth2 for Eventbrite #

# 1. load the API Client library #
include "../Eventbrite.php"; 

# 2. supply some functions to help with data-management tasks: #
// Define some $save and $delete, and $get callbacks 
//  that work well with your particular storage system.
//  This example uses PHP's built-in $_SESSION storage:
session_start();
$delete_access_token = function( $token=null ){
    // this function should default to removing the existing user's access_token
    //  Depending on how your app is constructed, you may want to include optional
    //  support for removing other access_tokens by name.
    unset($_SESSION['EB_OAUTH_ACCESS_TOKEN']);
};
$save_access_token = function( $access_token ){
    $_SESSION['EB_OAUTH_ACCESS_TOKEN'] = $access_token;
};
$get_access_token = function(){ 
    if(isset($_SESSION['EB_OAUTH_ACCESS_TOKEN'])){
        return $_SESSION['EB_OAUTH_ACCESS_TOKEN'];
    }else{
        return null;
    }
};

# 3. Set API key and other config variables, and update your API_Key's redirect_uri #
// Initialize the API client using one of the following combinations of info:
// 1- app_key : This information can be used to access public data, or to initiate an OAuth2 workflow
// 2- access_code, app_key, client_secret : an access_code is a temporary key
//    that can be exchanged for an access_token.  Save the access_token for 
//    future use.
// 3- access_token : try this first if you think you already have a valid access_token
$api_key = 'YOUR_APP_KEY';  // This data can be found at http://www.eventbrite.com/api/key
$client_secret = 'YOUR_CLIENT_SECRET';  // make sure that you have your redirect_uri set correctly
$access_token = $get_access_token();    // attempt to retreive this user's access_token from storage
$access_code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;    //Returning user with access_code?
$error_message = isset($_REQUEST['error']) ? $_REQUEST['error'] : null;//Returning user with access_denied?

// controller related work -
// replace this example with something that works with your app design:
if( isset($_GET['logout']) && $_GET['logout']=="true" ){ 
    $delete_access_token(); 
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// data / model related work:
//  package up our OAuth2 login data:
$auth_params = array( 'app_key'       => $api_key,
                      'client_secret' => $client_secret,
                      'access_token'  => $access_token,
                      'access_code'   => $access_code,
                      'error_message' => $error_message);

//pre-render the HTML that will be needed for this LoginWidget
// Supply callbacks that describe how to manage your data storage needs
$widget_html = Eventbrite::loginWidget($auth_params, $save_access_token, $delete_access_token);
?>
<html>
  <?=$widget_html?>
  <?= "This user's OAuth2.0 access_token is: " . $get_access_token();?>
</html>
