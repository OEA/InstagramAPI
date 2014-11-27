<?php
session_start();
/**
 * Instagram API class
 *
 * API Documentation: http://instagram.com/developer/
 * Class Documentation: https://github.com/OEASLAN/Instagram-PHP-API
 *
 * @author Ömer ASLAN
 * @since 27.11.2014
 * @copyright Ömer ASLAN
 * @version 1.0
 * @license GNU http://www.gnu.org/copyleft/gpl.html
 */

class Instagram{
  /**
   * The API base URL
   */
  const API_URL = 'https://api.instagram.com/v1/';
  /**
   * The API OAuth URL
   */
  const API_OAUTH_URL = 'https://api.instagram.com/oauth/authorize';
  /**
   * The OAuth token URL
   */
  const API_OAUTH_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';
  /**
   * API client ID
   *
   * @var string
   */
  private $clientId;
  /**
   * API client secret
   *
   * @var string
   */
  private $clientSecret;
  /**
   * The callback URL
   *
   * @var string
   */
  private $callbackUrl;
  /**
   * scopes
   *
   * @var array
   */
  private $scopes = array();
  /**
   * Available actions
   *
   * @var array
   */
  private $actions = array();
  /**
   * Errors list.
   *
   * @var array
   */
  private $errors = array(
      "construction"    => "Error: please check your configuration data.",
      "loginUrl"        => "Error: please check your scope permissions. You should set valid scopes.",
      "authentication"  => "Error: The method which you called ( -method_name- ) requires an authenticated users access token.",
      "oauth"           => "Error: oauthControl() - cURL error: ",
      "getdata"         => "Error: getData() - cURL error: ",
    );
  /**
   * Main constructor
   * @param $data : array
   * @return void
   */
  public function __construct($data){
    if (is_array($data)) {
      $this->setClientId($data['clientId']);
      $this->setClientSecret($data['clientSecret']);
      $this->setCallbackUrl($data['callbackUrl']);
      $this->setScopes($data['scopes']);
      $this->setActions($data['actions']);
    }else{
      throw new Exception($this->errors["construction"]);
    }
  }
  /**
   * Setting client ID
   * @param $clientId : string
   * @return void
   */
  private function setClientId($clientId){
    $this->clientId = $clientId;
  }
  /**
   * Setting client ID
   * @param $clientSecret : string 
   * @return void
   */
  private function setClientSecret($clientSecret){
    $this->clientSecret = $clientSecret;
  }
  /**
   * Setting callback URL
   * @param $callbackUrl : string
   * @return void
   */
  private function setCallbackUrl($callbackUrl){
    $this->callbackUrl = $callbackUrl;
  }
  /**
   * Setting scopes
   *
   * @param $scopes : array
   * @return void
   */
  private function setScopes($scopes){
    //Adding elements of scopes
    for($i=0;$i<count($scopes);$i++){
      $this->scopes[] = $scopes[$i];
    }
  }
  /**
   * Setting actions
   * @param $actions : array
   * @return void
   */
  private function setActions($actions){
    //Adding elements of actions
    for($i=0;$i<count($actions);$i++){
      $this->actions[] = $actions[$i];
    }
  }
  /**
   * Getting loginUrl
   * @return loginUrl : string
   */
  public function getLoginUrl(){
    if (is_array($this->scopes) && count($this->scopes)) {
      return self::API_OAUTH_URL . '?client_id=' . $this->getClientId() . '&redirect_uri=' . urlencode($this->getCallbackUrl()) . '&scope=' . implode('+', $this->scopes) . '&response_type=code';
    } else {
      throw new Exception($this->errors['loginUrl']);
    }
  }
  /**
   * Getting clientId
   * @return $this->clientId : string
   */
  private function getClientId(){
    return $this->clientId;
  }
  /**
   * Getting clientSecret
   * @return $this->clientSecret : string
   */
  private function getClientSecret(){
    return $this->clientSecret;
  }
  /**
   * Getting clientId
   * @return $this->callbackUrl : string
   */
  private function getCallbackUrl(){
    return $this->callbackUrl;
  }
  /**
   * Getting userInfo
   * @param $id : int
   * @return $data : object
   */
  public function getUser($id = 0){
    $auth = false;
    if ($id === 0 && (isset($_SESSION['accessToken']))) { $id = 'self'; $auth = true; }
    $data = $this->getData('users/' . $id, $auth);

    if($data->meta->code == 400){
      $this->setAccess(false);
    }
    return $data;
  }
  /**
   * Getting Followers list
   * @param $id : string
   * @return $data : object
   */
  public function getFollowers($id = 'self', $limit = 0) {
    return $this->getData('users/' . $id . '/followed-by', true, array('count' => $limit));
  }
  /**
   * The reading data method
   *
   * @param $api_file : string
   * @param $auth : boolean
   * @return jsonData
   */
  private function getData($api_file, $auth = false, $params = null, $method = 'GET') {
    if (!$auth) {
      $authUrl = '?client_id=' . $this->getClientId();
    } else {
      if (isset($_SESSION['accessToken'])) {
        $authUrl = '?access_token=' . $this->getAccessToken();
      } else {
        throw new Exception($this->errors['authentication']);
      }
    }
    if (isset($params) && is_array($params)) {
      $paramString = '&' . http_build_query($params);
    } else {
      $paramString = null;
    }
    $url = self::API_URL . $api_file . $authUrl . (($method=='GET') ? $paramString : null);
    $header = array('Accept: application/json');
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($method=='POST') {
      curl_setopt($ch, CURLOPT_POST, count($params));
      curl_setopt($ch, CURLOPT_POSTFIELDS, ltrim($paramString, '&'));
    } else if ($method=='DELETE') {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    $jsonData = curl_exec($ch);
    if (!$jsonData) {
      throw new Exception($this->errors['getdata']. curl_error($ch));
    }
    curl_close($ch);
    return json_decode($jsonData);
  }
  /**
   * Setting oAuth
   * @param $code : string
   * @param $token : boolean
   * @return void
   */
  public function setOAuthToken($code, $token = false) {
    $apiData = array(
      'grant_type'      => 'authorization_code',
      'client_id'       => $this->getClientId(),
      'client_secret'   => $this->getClientSecret(),
      'redirect_uri'    => $this->getCallbackUrl(),
      'code'            => $code
    );
    $result = $this->oauthControl($apiData);
    $_SESSION['accessToken'] = $result->access_token;
    $this->setAccess(true);
  }
  /**
   * Getting Followers list
   * @return $accessToken : string
   */
  private function getAccessToken(){
    return $_SESSION['accessToken'];
  }
  /**
   * OAuth control
   * @param $apiData : object
   * @return void
   */
  private function oauthControl($apiData) {
    $apiHost = self::API_OAUTH_TOKEN_URL;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiHost);
    curl_setopt($ch, CURLOPT_POST, count($apiData));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($apiData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $jsonData = curl_exec($ch);
    if (false === $jsonData) {
      throw new Exception($this->errors['oauth'] . curl_error($ch));
    }
    curl_close($ch);
    return json_decode($jsonData);
  }
  /**
   * Getting isAccesed
   * @return $isAccesed : boolean
   */
  public function isAccessed(){
    return (isset($_SESSION['isAccessed'])) ? $_SESSION['isAccessed']: false;
  }
  /**
   * Setting isAccessed session
   * @param $value : boolean
   * @return void
   */
  private function setAccess($value){
    $_SESSION['isAccessed'] = $value;
  }

}