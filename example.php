<?php
// load the API Client library
include "Eventbrite.php"; 

    /** 
     * Eventbrite API key (REQUIRED)
     *    http://www.eventbrite.com/api/key/
     **/
$app_key = 'YOUR_APP_KEY';
     
    /** 
     * Eventbrite user_key (OPTIONAL, only needed for reading/writing private user data)
     *     http://www.eventbrite.com/userkeyapi
     **/
$user_key = 'YOUR_USER_KEY';

// Initialize the API client
$eb_client = new Eventbrite( $app_key, $user_key );

// event_get example - http://developer.eventbrite.com/doc/events/event_get/
$resp = $eb_client->event_get( array('id'=>'1501016582') );
print( Eventbrite::ticketWidget($resp->event) );

// event_search example - http://developer.eventbrite.com/doc/events/event_search/
$search_params = array(
  'max' => 2,
  'city' => 'San Francisco',
  'region' => 'CA',
  'country' => 'US'
);
$resp = $eb_client->event_search($search_params);
var_dump( $resp);

// For more information about the features that are available through the Eventbrite API, see http://developer.eventbrite.com/doc/
?>
