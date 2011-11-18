<?php
# Getting Started with OAuth2 for Eventbrite #

# 1. load the API Client library #
include "../Eventbrite.php"; 

# 2. supply some functions to help with data-management tasks: #
// Define some $save, $delete, and $get callbacks 
//  that work well with your particular storage system.
//  This example uses PHP's built-in $_SESSION storage:
session_start();

# 3. Set API key and other config variables, and update your API_Key's redirect_uri #
// Initialize the API client using one of the following combinations of info:
// 1- app_key : This information can be used to access public data, or to initiate an OAuth2 workflow
// 2- access_code, app_key, client_secret : an access_code is a temporary key
//    that can be exchanged for an access_token.  Save the access_token for 
//    future use.
// 3- access_token : try this first if you think you already have a valid access_token
$api_key = 'YOUR_APP_KEY';  // This data can be found at http://www.eventbrite.com/api/key
$client_secret = 'YOUR_CLIENT_SECRET';  // make sure that you have your redirect_uri set correctly

// controller related work -
// replace this example with something that works with your app design:
if( isset($_GET['logout']) && $_GET['logout']=="true" ){ 
    Eventbrite::deleteAccessToken(); 
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// generate the HTML for this LoginWidget -
// Supply callbacks that describe how to manage your data storage needs
$widget_html = Eventbrite::loginWidget(array( 
    'app_key'       => $api_key,
    'client_secret' => $client_secret,
    'access_token'  => Eventbrite::getAccessToken(),
    'access_code'   => isset($_REQUEST['code']) ? $_REQUEST['code'] : null,
    'error_message' => isset($_REQUEST['error']) ? $_REQUEST['error'] : null));
?>
<html>
  <?=$widget_html?>
  <?= "This user's OAuth2.0 access_token is: " . $get_access_token();?>
</html>
