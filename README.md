#PHP Eventbrite API Client Library
----------------------------------
# Requirements: #
### API key ###
Eventbrite API keys are available here: http://www.eventbrite.com/api/key/
### User key ###
Eventbrite User_keys are optional.  They are only required if you need to access private data.  Eventbrite users can find their user_key here: 
http://www.eventbrite.com/userkeyapi

# Examples: #
### Require the API Client code ###

    require 'Eventbrite.php';

### Initialize the client ###

    // add your authentication tokens below:
    $eb_client = new Eventbrite( 'APP_KEY', 'USER_KEY' );

### event_get example ###

    // request an event by adding a valid EVENT_ID value here:
	$resp = $eb_client->event_get( array('id' => 'EVENT_ID') );

    // print a ticket widget for the event:
    print( Eventbrite::ticketWidget($resp->event) );

### event_search example ###

    $search_params = array(
        'max' => 2,
        'city' => 'San Francisco',
        'region' => 'CA',
        'country' => 'US'
    );
	$resp = $eb_client->event_search( $search_params );

## More information about available API methods
Eventbrite API documentation:  http://developer.eventbrite.com/doc

# Resources: #
* <a href="http://eventbrite.github.com/">Eventbrite on GitHub</a>
* <a href="http://developer.eventbrite.com/doc/">API Documentation</a>
* <a href="http://developer.eventbrite.com/doc/getting-started/">API Getting-Started Guide</a>
* <a href="http://developer.eventbrite.com/terms/">Eventbrite API terms and usage limitations</a>
* <a href="http://developer.eventbrite.com/news/branding/">Eventbrite Branding Guidelines</a>
