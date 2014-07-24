<?php
/**
*     @author Dimmduh
*     @email dimmduh@gmail.com
*/
class API{
    
    private $service;
    private $auth;
    private $token;
    private $source = 'GoogleReaderAPIClass-0.1';
    private $client = 'scroll';
    private $account_type = 'HOSTED_OR_GOOGLE';
    private $clientlogin_url = 'https://www.google.com/accounts/ClientLogin';
    private $reader_api_url = 'http://www.google.com/reader/api/0/';
    private $session_var_auth_name = 'google_auth';

    
    public function __construct( $email, $password, $service = 'reader' ){
        if (isset( $service ) ){
            $this -> service = $service;
        }
        /* if ( isset($_SESSION[ $this -> session_var_auth_name ] ) ){
            $this -> auth = $_SESSION[ $this -> session_var_auth_name ];
            echo "Loading";
        } else { */
            $this -> clientLogin( $email, $password );
            $this -> get_token();
        /* } */
     }
          
     
    private function request( $url, $type = 'get', $headers = false, $fields = false, $cookie = false){
     
          $curl = curl_init();
                    
          if ( $fields ){
               if ($type == 'get'){
                    $url .= '?'.http_build_query( $fields );
               } else {
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields) );
               }
          }
          if ( $headers ){
               curl_setopt($curl, CURLOPT_HEADER, true);
               curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
          }
           if ( $cookie ){
               curl_setopt($curl, CURLOPT_COOKIE, $cookie);
          }
          
          curl_setopt($curl, CURLOPT_URL, $url);
          if (strpos($url, 'https://') !== false){
               curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          }
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($curl, CURLINFO_HEADER_OUT, true);
          
          $response = array();
          $response['text'] = curl_exec($curl);
          $response['info'] = curl_getinfo( $curl);
          $response['code'] = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
          $response['body'] = substr( $response['text'], $response['info']['header_size'] );

          curl_close( $curl );
          return $response;
     }

     private function request2google( $url, $type = 'get', $headers = false, $fields = false ){
          if ( $this -> auth ){
               $headers[] = 'Content-type: application/x-www-form-urlencoded';
               $headers[] = 'Authorization: GoogleLogin auth='.$this -> auth;
               
               if ( strpos( $url, 'http://') === false && strpos( $url, 'https://' ) === false ){
                    $url = $this -> reader_api_url.$url;
               }
               
               $response = $this -> request( $url, $type, $headers, $fields);               
               if ( $response['code'] == 200 ){
                    if ( isset( $fields['output'] ) ){
                         switch ($fields['output']){
                              case 'xml':
                                   return (new SimpleXMLElement( $response['body'] ) );
                                   break;
                              case 'json':
                              default:
                                   return json_decode( $response['body'] );
                                   break;
                         }
                    } else {
                         return $response['body'];
                    }
               } else {
                    Throw new AutentificationException('Auth error: server response '.$response['code'] );
               }
               
          } else {
               Throw new AutentificationException('Auth error: not finded Auth token');
          }
     }
     
     public function get_tag_list( $output = 'json' ){
          return $this -> request2google('tag/list', "get", false, array(
                    'output' => $output,
                    'ck' => time(),
                    'client' => $this -> client,
               ));
     }
     public function get_subscription_list( $output = 'json' ){
          return $this -> request2google('subscription/list', "get", false, array(
                    'output' => $output,
                    'ck' => time(),
                    'client' => $this -> client,
               ));
     }
     public function get_preference_list( $output = 'json' ){
          return $this -> request2google('preference/list', "get", false, array(
                    'output' => $output,
                    'ck' => time(),
                    'client' => $this -> client,
               ));
     }
     public function get_unread_count( $output = 'json' ){
          return $this -> request2google('unread-count', "get", false, array(
                    'all' => true,
                    'output' => $output,
                    'ck' => time(),
                    'client' => $this -> client,
               ));
     }
     public function get_user_info( $output = 'json' ){
          return $this -> request2google('user-info', "get", false, array(
                    'output' => $output,
                    'ck' => time(),
                    'client' => $this -> client,
               ));
     }
     private function get_token(){
          $this -> token = $this -> request2google('token');
     }
     
     //get contents functions
     /*
          r - order
          r = n - new items
          r = o - old items
          r = a - auto sort
     
     */
     private function get_content( $content_url = '', $number = 20, $order = 'n', $exclude_target = '', $start_time = '', $continuation = ''){
          $fields = array(
               'ck' => time(),
               'client' => $this -> client,
               'n' => $number,
               'r' => $order,
               'output' => 'json',
          );
          if ( !empty($exclude_target) ){$fields['xt'] = $exclude_target;}
          if ( !empty($start_time) ){$fields['ot'] = $start_time;}
          if ( !empty($continuation) ){$fields['c'] = $continuation;}
          
          return $this -> request2google('stream/contents/'.Utils::urlencode( $content_url ), 'get', false, $fields);
     }
     
     public function get_content_feed( $feed_url = '', $number = 20, $order = 'n', $exclude_target = '', $start_time = '', $continuation = ''){
          return $this -> get_content( $feed_url, $number, $order, $exclude_target, $start_time, $continuation );
     }
     public function get_content_by_label( $label = '', $number = 20, $order = 'n', $exclude_target = '', $start_time = '', $continuation = ''){
          return $this -> get_content( (strpos($label, '/') === false?'user/-/label/':'').$label, $number, $order, $exclude_target, $start_time, $continuation );
     }
     public function get_content_by_state( $state = '', $number = 20, $order = 'n', $exclude_target = '', $start_time = '', $continuation = ''){
          return $this -> get_content( (strpos($state, '/') === false?'user/-/state/com.google/':'').$state, $number, $order, $exclude_target, $start_time, $continuation );
     }

    public function get_unread( $number = 20, $order = 'n' ){
        return $this ->get_content_by_state('reading-list', $number, $order, 'user/-/state/com.google/read');
    }

    public function get_starred($number = 20, $order = 'n'){
        return $this ->get_content_by_state('starred', $number, $order);
    }

    /*
     Edit functions
     */
     private function edit_do( $api_function , $post_fields ){
          $post_fields['T'] = $this -> token;
          if ( $this -> request2google( $api_function, "post", false, $post_fields ) == "OK"){
                    return true;
                } else {
                    return false;
                }
     }
     
     /* public function edit_subscription( 
     s     return $this -> edit_do( 'subscription/edit', 
     } */
     public function set_state( $itemId, $state = 'read'){
            $post_fields = array(
                "i" => $itemId,
                "a" => 'user/-/state/com.google/'.$state,
            );
            //print_r( $post_fields );
            return $this ->edit_do('edit-tag?client='.$this -> client, $post_fields);
        }

     private function clientLogin( $email, $password ){
          
          $response = $this -> request( $this -> clientlogin_url, 'post', false, array(
               "accountType" => $this -> account_type,
               "Email" => $email,
               "Passwd" => $password,
               "service" => $this -> service,
               "source" => $this -> source,
          ));
                    
          if ( $response['code'] == 200) {
               preg_match("/Auth=([a-z0-9_\-]+)/i", $response['body'], $matches_auth);               
               if ($matches_auth[1]){
                    $this -> auth = $matches_auth[1];
                    $_SESSION[ $this -> session_var_auth_name ] = $this -> auth;
                    return true;
               } else {
                    Throw new AutentificationException('Auth error: not finded Auth token in response');
               }
          } else {
               Throw new AutentificationException('Auth error: server response '.$response['code'] );
          }
     }
}

//Exceptions
class AutentificationException extends Exception {}