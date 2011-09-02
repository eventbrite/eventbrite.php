#Display your event list in HTML#
The easiest way to display an event on your existing website is to use one of Eventbrite's available [widgets](http://www.eventbrite.com/t/promote-your-event-with-widgets).  However, if you organize a lot of events, and are looking for a way to automatically keep your site up-to-date using PHP, then this guide should come in handy.

##Install an API client##
First, download the [PHP Eventbrite API client](https://raw.github.com/ryanjarvinen/eventbrite.php/master/Eventbrite.php) and add it to your site's codebase.

##Initialize the API client##
Load the API Client library, and add your Eventbrite authentication tokens to initialize the client.
Your [API / Application key is required](http://www.eventbrite.com/api/key/).  Adding an Eventbrite [user_key]( http://www.eventbrite.com/userkeyapi) is only required for accessing private events or other user data.

    <?php
        include "Eventbrite.php"; 

        $authentication_tokens = array(
            'app_key'  => 'YOUR_APP_KEY',
            'user_key' => 'YOUR_USER_KEY');

        $eb_client = new Eventbrite( $authentication_tokens );
    ?>

##Access your list of events##
For more information about the functions that are available through the Eventbrite API, see [the Eventbrite API documentation](http://developer.eventbrite.com/doc/)
For this example, we will use the [user_list_events]( http://developer.eventbrite.com/doc/users/user_list_events/ ) API method.
You could also use the [event_search]( http://developer.eventbrite.com/doc/events/event_search/ ) or [organizer_list_events]( http://developer.eventbrite.com/doc/organizers/organizer_list_events/ ) API methods, depending on your needs.

    <?php 
        try {
            $events = $eb_client->user_list_events();
        } catch ( Exception $e ) {
            // Be sure to plan for potential error cases 
            // so that your application can respond appropriately

            //var_dump($e);
            $events = array();
        }
    ?>

##Display your event listing as HTML##
Now that you have your events, convert them to HTML for display

    <?= Eventbrite::eventList( $events, 'eventListRow'); ?>

The resulting HTML should look similar to this:

    <div class="eb_event_list">
        <div class='eb_event_list_item' id='evnt_div_1485261457'>
            <span class='eb_event_list_date'>Tue, May  4</span>
            <span class='eb_event_list_time'> 1:00 pm</span>
            <a class='eb_event_list_title' href='http://www.eventbrite.com/event/1485261457'>API_EVENT_TEST2</a>
            <span class='eb_event_list_location'>my place</span>
        </div>
        <div class='eb_event_list_item' id='evnt_div_1472940605'>
            <span class='eb_event_list_date'>Tue, May  3</span>
            <span class='eb_event_list_time'> 1:00 pm</span>
            <a class='eb_event_list_title' href='http://www.eventbrite.com/event/1485261457'>API_EVENT_TEST1</a>
            <span class='eb_event_list_location'>my place</span>
        </div>
    </div>

##Customize the event listing by adding your own CSS##
This example stylesheet template should help you get started.

    <style type="text/css">
    .eb_event_list_item{
      padding-top: 20px;
    }
    .eb_event_list_title{
      position: absolute;
      left: 220px;
      width: 300px;
      overflow: hidden;
    }
    .eb_event_list_date{
      padding-left: 20px;
    }
    .eb_event_list_time{
      position: absolute;
      left: 150px;
    }
    .eb_event_list_location{
      position: absolute;
      left: 520px;
    }
    </style>

##Additional event list customization##
If this guide's resulting HTML event list does not meet your needs, you can always create your own function to convert the API responses into HTML.

... more information coming soon ...
