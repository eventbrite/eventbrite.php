<?php
class Eventbrite {
    /**
     * Eventbrite API endpoint
     */
    var $api_endpoint = "https://www.eventbrite.com/json/";

    /**
     * Eventbrite API key (REQUIRED)
     *    http://www.eventbrite.com/api/key/
     * Eventbrite user_key (OPTIONAL, only needed for reading/writing private user data)
     *     http://www.eventbrite.com/userkeyapi
     *
     * Alternate authorization parameters (instead of user_key):
     *   Eventbrite user email
     *   Eventbrite user password
     */
    function Eventbrite( $tokens = null, $user = null, $password = null ) {
        $this->api_url = parse_url($this->api_endpoint);
        $this->auth_tokens = array();
        if(is_array($tokens)){
            if(array_key_exists('access_code', $tokens)){
                $this->auth_tokens = $this->oauth_handshake( $tokens );
            }else{
                $this->auth_tokens = $tokens;
            }
        }else{
            $this->auth_tokens['app_key'] = $tokens;
            if( $password ){
                $this->auth_tokens['user'] = $user;
                $this->auth_tokens['password'] = $password;
            }
            else {
              $this->auth_tokens['user_key'] = $user;
            }
        }
    }

    function oauth_handshake( $tokens ){
        $params = array( 
            'grant_type'=>'authorization_code', 
            'client_id'=> $tokens['app_key'], 
            'client_secret'=> $tokens['client_secret'], 
            'code'=> $tokens['access_code'] );

        $request_url = $this->api_url['scheme'] . "://" . $this->api_url['host'] . '/oauth/token';
        
        // TODO: Replace the cURL code with something a bit more modern - 
        //$context = stream_context_create(array('http' => array( 
        //    'method'  => 'POST', 
        //    'header'  => "Content-type: application/x-www-form-urlencoded\r\n", 
        //    'content' => http_build_query($params)))); 
        //$json_data = file_get_contents( $request_url, false, $context );

        // CURL-POST implementation - 
        // WARNING: This code may require you to install the php5-curl package
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_URL, $request_url); 
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $json_data = curl_exec($ch);
        $resp_info = curl_getinfo($ch);
        curl_close($ch); 

        $response = get_object_vars(json_decode($json_data));
        if( !array_key_exists('access_token', $response) || array_key_exists('error', $response) ){
            throw new Exception( $response['error_description'] );
        }
        return array_merge($tokens, $response);
    }

    // For information about available API methods, see: http://developer.eventbrite.com/doc/
    function __call( $method, $args ) {  
        // Unpack our arguments
        if( is_array( $args ) && array_key_exists( 0, $args ) && is_array( $args[0]) ){
            $params = $args[0];
        }else{
            $params = array();
        }
        
        // Add authentication tokens to querystring
        if(!isset($this->auth_tokens['access_token'])){
            $params = array_merge($params, $this->auth_tokens);
        }
        
        // Build our request url, urlencode querystring params 
        $request_url = $this->api_url['scheme']."://".$this->api_url['host'].$this->api_url['path'].$method.'?'.http_build_query( $params,'','&');
        
        // Call the API
        if(!isset($this->auth_tokens['access_token'])){
            $resp = file_get_contents( $request_url );
        }else{
            $options = array(
                'http'=>array( 'method'=> 'GET',
                               'header'=> "Authorization: Bearer " . $this->auth_tokens['access_token'])
            );
            $resp = file_get_contents( $request_url, false, stream_context_create($options));
        }
        
        // parse our response
        if($resp){
            $resp = json_decode( $resp );
        
            if( isset( $resp->error ) && isset($resp->error->error_message) ){
                throw new Exception( $resp->error->error_message );
            }
        }
        return $resp;
    }

    /*
     * Helpers:
     */
    public static function OAuthLogin($auth_tokens, $save_token, $delete_token){
        $user = false;
        $response = array();
        # Attempt to authenticate this user using an access_token, if provided
        if( isset($auth_tokens['access_token']) ){
            try{
                // Example using an access_token to initialize the API client:
                $eb = new Eventbrite(array('access_token' => $auth_tokens['access_token']));
                $user = $eb->user_get()->user;
            }catch(Exception $e){
                $user = false;
                // This token may no longer be valid
                //   refresh it, or clear it
                $response['login_error'] = "Access Denied";
                $delete_token( $auth_tokens['access_token'] );
            }
        }

        # We do not have a valid access token for this user so far
        if( $user == false ){
            # This user is not yet authenticated - 
            #    it is their first visit, 
            #    or they are returning with an access_code that we will exchange for an access_token,
            #    or they were redirected here after logout
            if( isset($auth_tokens['access_code']) ){
                # This user has just authenticated, get their access token and store it
                try{
                    $eb = new Eventbrite($auth_tokens );
                    $response['access_token'] = $eb->auth_tokens['access_token'];
                    // save this access_token for future use!
                    $save_token( $response['access_token'] );
                    header('Location: ' . $_SERVER['PHP_SELF'] );
                    exit;
                }catch (Exception $e){
                    $response['login_error'] = $e->error->error_message;
                }
            }else if( isset($auth_tokens['error_message'] )){
                $response['login_error'] = $auth_tokens['error_message'];
            }
        }else if(is_object($user)){
            $response['user_email'] = $user->email;
            $response['user_name'] = $user->first_name . ' ' . $user->last_name;
        }
        return $response;
    }

    public static function loginHTML( $params ){
    // Replace this example with something that works with your Application's templating engine
        $html = "<div class='eb_login_widget'> <h2>Eventbrite Account Access</h2>";
        if( isset($params['user_name']) && isset($params['user_email']) && isset($params['logout_link']) ){
            $html .= "<div><h3>Welcome Back</h3>";
            $html .= "<p>You are logged in as:<br/>{$params['user_name']}<br/><i>({$params['user_email']})</i></p>";
            $html .= "<p><a class='button' href='{$params['logout_link']}'>Logout</a></p></div>";
      
        }elseif( isset($params['oauth_link']) ){
            $html .= "<div><h3>Authenticate with Eventbrite</h3>";
            if(isset($params['login_error'])){
                $html .= "<p class='error'>{$params['login_error']}</p>";
            }
            $html .= "<p>In order to help you manage your events, we will need access to your <a href='http://eventbrite.com'>Eventbrite</a> account data.</p>";
            $html .= "<p><a class='button' href='{$params['oauth_link']}'>Connect with Eventbrite</a></p></div>";
        }else{
            $html .= "<div><h2>Error initializing Eventbrite loginHTML example template.</h2></div>";
        }  
        $html .= "</div>";
        return $html;
    }

    public static function oauthNextStep( $key ) {
        return 'https://www.eventbrite.com/oauth/authorize?response_type=code&client_id='.$key;
    }

    public static function eventList($evnts= array(), $callback='eventListRow', $options=false) {
        $html='<div class="eb_event_list">';
        if( isset($evnts->events)){
            foreach( $evnts->events as $evnt ){
                if( isset($evnt->event ) ){
                     if(is_callable($callback)){
                         if($options){
                             $html .= $callback($evnt->event, $options);
                         }else{
                             $html .= $callback($evnt->event);
                         }
                     }else if(is_callable( array('self', $callback))){
                         if($options){
                             $html .= self::$callback($evnt->event, $options);
                         }else{
                             $html .= self::$callback($evnt->event);
                         }
                     }
                }
            }
        }else{
            $html .= "No events were found at this time.";
        }
        return $html . "</div>";
    }

    public static function eventListRow( $evnt ) {
        $time = strtotime($evnt->start_date);
        $venue_name = 'online';
        if( isset($evnt->venue) && isset( $evnt->venue->name )){
            $venue_name = $evnt->venue->name;
        }

        return "<div class='eb_event_list_item' id='evnt_div_" . $evnt->id ."'><span class='eb_event_list_date'>" . strftime('%a, %B %e', $time) . "</span><span class='eb_event_list_time'>" . strftime('%l:%M %P', $time) . "</span>" ."<a class='eb_event_list_title' href='".$evnt->url."'>".$evnt->title."</a><span class='eb_event_list_location'>" . $venue_name . "</span></div>\n";
    }
    /*
     * Widgets:
     */
    public static function loginWidget( $auth_params, $save_access_token, $delete_access_token ){
        //  Check to see if we have a valid user account:
        $response = Eventbrite::OAuthLogin($auth_params, $save_access_token, $delete_access_token);
        $login_params = array();
        
        //  package up the data for our view / template:
        if( is_array($response)){
            if( isset( $response['user_email']) ){
                $login_params = array('oauth_link' => Eventbrite::oauthNextStep($auth_params['app_key']),
                                      'user_name'  => $response['user_name'],
                                      'user_email' => $response['user_email'],
                                      'logout_link'=> $_SERVER['PHP_SELF'] . '?logout=true');
            }else{
                $login_params = array('oauth_link' => Eventbrite::oauthNextStep($auth_params['app_key']));
            }
            if(isset( $response['login_error'])){
                $login_params['login_error'] = $response['login_error'];
            }
        }
        
        // view related work:
        //  render your "template"
        return Eventbrite::loginHTML( $login_params );  
    }

    public static function ticketWidget( $evnt ) {
        return '<div style="width:100%; text-align:left;" ><iframe src="http://www.eventbrite.com/tickets-external?eid=' . $evnt->id . '&ref=etckt" frameborder="0" height="192" width="100%" vspace="0" hspace="0" marginheight="5" marginwidth="5" scrolling="auto" allowtransparency="true"></iframe><div style="font-family:Helvetica, Arial; font-size:10px; padding:5px 0 5px; margin:2px; width:100%; text-align:left;" ><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/r/etckt" >Online Ticketing</a><span style="color:#ddd;" > for </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/event/' . $evnt->id . '?ref=etckt" >' . $evnt->title . '</a><span style="color:#ddd;" > powered by </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com?ref=etckt" >Eventbrite</a></div></div>';
    }

    public static function registrationWidget( $evnt ) {
        return '<div style="width:100%; text-align:left;" ><iframe src="http://www.eventbrite.com/event/' . $evnt->id . '?ref=eweb" frameborder="0" height="1000" width="100%" vspace="0" hspace="0" marginheight="5" marginwidth="5" scrolling="auto" allowtransparency="true"></iframe><div style="font-family:Helvetica, Arial; font-size:10px; padding:5px 0 5px; margin:2px; width:100%; text-align:left;" ><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/r/eweb" >Online Ticketing</a><span style="color:#ddd;" > for </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/event/' . $evnt->id . '?ref=eweb" >' . $evnt->title . '</a><span style="color:#ddd;" > powered by </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com?ref=eweb" >Eventbrite</a></div></div>';

    }

    public static function calendarWidget( $evnt ) {
        return '<div style="width:195px; text-align:center;" ><iframe src="http://www.eventbrite.com/calendar-widget?eid=' . $evnt->id . '" frameborder="0" height="382" width="195" marginheight="0" marginwidth="0" scrolling="no" allowtransparency="true"></iframe><div style="font-family:Helvetica, Arial; font-size:10px; padding:5px 0 5px; margin:2px; width:195px; text-align:center;" ><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/r/ecal">Online event registration</a><span style="color:#ddd;" > powered by </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com?ref=ecal" >Eventbrite</a></div></div>';
    }

    public static function countdownWidget( $evnt ) {
        return '<div style="width:195px; text-align:center;" ><iframe src="http://www.eventbrite.com/countdown-widget?eid=' . $evnt->id . '" frameborder="0" height="479" width="195" marginheight="0" marginwidth="0" scrolling="no" allowtransparency="true"></iframe><div style="font-family:Helvetica, Arial; font-size:10px; padding:5px 0 5px; margin:2px; width:195px; text-align:center;" ><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/r/ecount" >Online event registration</a><span style="color:#ddd;" > for </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/event/' . $evnt->id . '?ref=ecount" >' . $evnt->title . '</a></div></div>'; 
    }

    public static function buttonWidget( $evnt ) {
        return '<a href="http://www.eventbrite.com/event/' . $evnt->id . '?ref=ebtn" target="_blank"><img border="0" src="http://www.eventbrite.com/registerbutton?eid=' . $evnt->id . '" alt="Register for ' . $evnt->title . ' on Eventbrite" /></a>';
    }

    public static function linkWidget( $evnt, $text=null, $color=null ) {
        return '<a href="http://www.eventbrite.com/event/' . $evnt->id . '?ref=elink" target="_blank" style="color:' . ( $color ? $color : "#000000" ) . ';">' . ( $text ? $text : $evnt->title ) . '</a>';
    }
};
?>
