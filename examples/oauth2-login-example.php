<?php
# Getting Started with OAuth2.0 for Eventbrite in 5 easy steps #

# 1. load the API Client library:
include "../Eventbrite.php"; 

# 1a. (optional) This example uses PHP's built-in $_SESSION storage:
// See the README file for information about integrating with other data-stores.
// This line may be needed to enable session support on your server:
session_start();

# 2. Set your API_Key and other required config variables:
//You can request an API_Key at http://www.eventbrite.com/api/key
$api_key = 'YOUR_APP_KEY';  
//Make sure to keep your "client_secret" a secret
$client_secret = 'YOUR_CLIENT_SECRET'; 
// To comply with our developer terms, your user's "access_tokens" 
// should be protected, and should not be exposed to other users.

# 3. Integrate this work into your controller or routing code:
if( isset($_GET['eb_logout']) && $_GET['eb_logout']=="true" ){ 
    // clear this user's access_token -
    Eventbrite::deleteAccessToken(); 
    // remove our "logout=true" trigger from the querystring-
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

# 4. Update your API_Key's "redirect_uri" setting on http://eventbrite.com/api/key
// Your redirect_uri should be set to a page on your site where this widget is accessible.

# 5. Finally, create a login widget - Like this:
?>
<html>
  <?= Eventbrite::loginWidget(array( 'app_key' => $api_key,
                               'client_secret' => $client_secret));?>

  <?    //additional debug output:
    if ( Eventbrite::getAccessToken()){
      print "<p>This user's OAuth2.0 access_token is: " . Eventbrite::getAccessToken() . "</p>";
    } 
  ?>
</html>
