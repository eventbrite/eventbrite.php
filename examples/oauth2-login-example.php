<?php
# Getting Started with OAuth2 for Eventbrite #

# 1. load the API Client library #
include "../Eventbrite.php"; 

# 1a. (optional) This example uses PHP's built-in $_SESSION storage:
// See the README file for information about integrating with other data-stores.
// This line may be needed to enable session support on your server:
session_start();

# 2. Set API key and other config variables
//You can request an API_Key at http://www.eventbrite.com/api/key
$api_key = 'YOUR_APP_KEY';  
//Make sure to keep your "client secret" a secret.
$client_secret = 'YOUR_CLIENT_SECRET'; 
// To comply with our developer terms, your user's "access_tokens" 
// should be protected, and should not be exposed to other users.

# 3. Integrate this work into your controller or routing code:
if( isset($_GET['logout']) && $_GET['logout']=="true" ){ 
    Eventbrite::deleteAccessToken(); 
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
# 4. Update your API_Key's redirect_uri setting on eventbrite.com
# 5. Create a login widget, like this:
?>
<html>
  <?= Eventbrite::loginWidget(array(
                  'app_key' => $api_key,
                  'client_secret' => $client_secret));?>

  <?//additional debug output:
    if ( Eventbrite::getAccessToken()){
      print "<p>This user's OAuth2.0 access_token is: " . Eventbrite::getAccessToken() . "</p>";
    } 
  ?>
</html>
